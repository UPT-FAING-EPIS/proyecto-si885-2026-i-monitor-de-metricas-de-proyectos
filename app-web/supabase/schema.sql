create extension if not exists pgcrypto;

create table if not exists public.profiles (
  id uuid primary key references auth.users(id) on delete cascade,
  email text not null unique,
  created_at timestamptz not null default now()
);

alter table public.profiles enable row level security;

create policy "profiles_select_self"
on public.profiles
for select
to public
using (id = auth.uid());

create policy "profiles_update_self"
on public.profiles
for update
to public
using (id = auth.uid())
with check (id = auth.uid());

create policy "profiles_insert_self"
on public.profiles
for insert
to public
with check (id = auth.uid());

create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  perform set_config('row_security', 'off', true);
  insert into public.profiles (id, email)
  values (new.id, new.email)
  on conflict (id) do update set email = excluded.email;
  return new;
end;
$$;

drop trigger if exists on_auth_user_created on auth.users;
create trigger on_auth_user_created
after insert on auth.users
for each row execute procedure public.handle_new_user();

insert into public.profiles (id, email)
select u.id, u.email
from auth.users u
where u.email is not null
on conflict (id) do update set email = excluded.email;

create or replace function public.get_user_id_by_email(email_in text)
returns table(id uuid)
language plpgsql
security definer
set search_path = public
as $$
begin
  perform set_config('row_security', 'off', true);
  return query
  select p.id
  from public.profiles p
  where lower(p.email) = lower(email_in)
  limit 1;
end;
$$;

revoke all on function public.get_user_id_by_email(text) from public;
grant execute on function public.get_user_id_by_email(text) to authenticated;

create or replace function public.ensure_my_profile()
returns void
language plpgsql
security definer
set search_path = public
as $$
declare
  v_uid uuid;
  v_email text;
begin
  v_uid := auth.uid();
  if v_uid is null then
    raise exception 'No autenticado.';
  end if;

  v_email := current_setting('request.jwt.claim.email', true);

  perform set_config('row_security', 'off', true);
  insert into public.profiles (id, email)
  values (v_uid, coalesce(v_email, 'unknown+' || v_uid::text || '@local.invalid'))
  on conflict (id) do update
  set email = excluded.email;
end;
$$;

revoke all on function public.ensure_my_profile() from public;
grant execute on function public.ensure_my_profile() to authenticated;

create table if not exists public.projects (
  id uuid primary key default gen_random_uuid(),
  owner_id uuid not null default auth.uid() references public.profiles(id) on delete cascade,
  name text not null,
  description text null,
  created_at timestamptz not null default now()
);

alter table public.projects enable row level security;

create policy "projects_select_owner"
on public.projects
for select
to authenticated
using (owner_id = auth.uid());

create policy "projects_insert_owner"
on public.projects
for insert
to authenticated
with check (owner_id = auth.uid());

create policy "projects_update_owner"
on public.projects
for update
to authenticated
using (owner_id = auth.uid())
with check (owner_id = auth.uid());

create policy "projects_delete_owner"
on public.projects
for delete
to authenticated
using (owner_id = auth.uid());

drop view if exists public.v_project_owner;

create table if not exists public.v_project_owner (
  project_id uuid primary key references public.projects(id) on delete cascade,
  owner_id uuid not null references public.profiles(id) on delete cascade
);

insert into public.v_project_owner (project_id, owner_id)
select p.id, p.owner_id
from public.projects p
on conflict (project_id) do update
set owner_id = excluded.owner_id;

create or replace function public.sync_v_project_owner()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  if (tg_op = 'DELETE') then
    delete from public.v_project_owner v where v.project_id = old.id;
    return old;
  end if;

  insert into public.v_project_owner (project_id, owner_id)
  values (new.id, new.owner_id)
  on conflict (project_id) do update
  set owner_id = excluded.owner_id;

  return new;
end;
$$;

drop trigger if exists projects_sync_v_project_owner on public.projects;
create trigger projects_sync_v_project_owner
after insert or update or delete on public.projects
for each row execute procedure public.sync_v_project_owner();

alter table public.v_project_owner enable row level security;

create policy "v_project_owner_select_self"
on public.v_project_owner
for select
to authenticated
using (owner_id = auth.uid());

revoke all on public.v_project_owner from public;
grant select on public.v_project_owner to authenticated;

create table if not exists public.tasks (
  id uuid primary key default gen_random_uuid(),
  project_id uuid not null references public.projects(id) on delete cascade,
  title text not null,
  description text null,
  status text not null default 'pending' check (status in ('pending','in_progress','completed')),
  estimated_minutes int null check (estimated_minutes is null or estimated_minutes >= 0),
  due_date date null,
  assignee_id uuid not null references public.profiles(id) on delete restrict,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists tasks_project_id_idx on public.tasks(project_id);
create index if not exists tasks_assignee_id_idx on public.tasks(assignee_id);

create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

drop trigger if exists tasks_set_updated_at on public.tasks;
create trigger tasks_set_updated_at
before update on public.tasks
for each row execute procedure public.set_updated_at();

create or replace function public.enforce_task_update_permissions()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
  v_project_owner uuid;
begin
  select p.owner_id into v_project_owner
  from public.projects p
  where p.id = new.project_id;

  if auth.uid() = v_project_owner then
    return new;
  end if;

  if auth.uid() = old.assignee_id then
    if new.project_id <> old.project_id
      or new.title <> old.title
      or coalesce(new.description,'') <> coalesce(old.description,'')
      or coalesce(new.estimated_minutes, -1) <> coalesce(old.estimated_minutes, -1)
      or coalesce(new.due_date::text, '') <> coalesce(old.due_date::text, '')
      or new.assignee_id <> old.assignee_id
    then
      raise exception 'Solo puedes actualizar el estado de tu tarea asignada.';
    end if;
    return new;
  end if;

  raise exception 'No autorizado.';
end;
$$;

drop trigger if exists tasks_enforce_update_permissions on public.tasks;
create trigger tasks_enforce_update_permissions
before update on public.tasks
for each row execute procedure public.enforce_task_update_permissions();

alter table public.tasks enable row level security;

create policy "tasks_select_owner_or_assignee"
on public.tasks
for select
to authenticated
using (
  exists (
    select 1 from public.projects p
    where p.id = tasks.project_id and p.owner_id = auth.uid()
  )
  or assignee_id = auth.uid()
);

create policy "tasks_insert_owner"
on public.tasks
for insert
to authenticated
with check (
  exists (
    select 1 from public.projects p
    where p.id = tasks.project_id and p.owner_id = auth.uid()
  )
);

create policy "tasks_update_owner_or_assignee"
on public.tasks
for update
to authenticated
using (
  exists (
    select 1 from public.projects p
    where p.id = tasks.project_id and p.owner_id = auth.uid()
  )
  or assignee_id = auth.uid()
)
with check (
  exists (
    select 1 from public.projects p
    where p.id = tasks.project_id and p.owner_id = auth.uid()
  )
  or assignee_id = auth.uid()
);

create policy "tasks_delete_owner"
on public.tasks
for delete
to authenticated
using (
  exists (
    select 1 from public.projects p
    where p.id = tasks.project_id and p.owner_id = auth.uid()
  )
);

create table if not exists public.time_entries (
  id uuid primary key default gen_random_uuid(),
  task_id uuid not null references public.tasks(id) on delete cascade,
  user_id uuid not null default auth.uid() references public.profiles(id) on delete cascade,
  minutes int not null check (minutes > 0),
  notes text null,
  created_at timestamptz not null default now()
);

create index if not exists time_entries_task_id_idx on public.time_entries(task_id);
create index if not exists time_entries_user_id_idx on public.time_entries(user_id);

alter table public.time_entries enable row level security;

create policy "time_entries_select_owner_or_self"
on public.time_entries
for select
to authenticated
using (
  user_id = auth.uid()
  or exists (
    select 1
    from public.tasks t
    join public.projects p on p.id = t.project_id
    where t.id = time_entries.task_id and p.owner_id = auth.uid()
  )
);

create policy "time_entries_insert_assignee"
on public.time_entries
for insert
to authenticated
with check (
  user_id = auth.uid()
  and exists (
    select 1 from public.tasks t
    where t.id = time_entries.task_id and t.assignee_id = auth.uid()
  )
);

create policy "time_entries_delete_self"
on public.time_entries
for delete
to authenticated
using (user_id = auth.uid());

create table if not exists public.task_evidences (
  id uuid primary key default gen_random_uuid(),
  task_id uuid not null references public.tasks(id) on delete cascade,
  user_id uuid not null default auth.uid() references public.profiles(id) on delete cascade,
  storage_path text not null,
  filename text not null,
  created_at timestamptz not null default now()
);

create index if not exists task_evidences_task_id_idx on public.task_evidences(task_id);
create index if not exists task_evidences_user_id_idx on public.task_evidences(user_id);

alter table public.task_evidences enable row level security;

create policy "task_evidences_select_owner_or_assignee"
on public.task_evidences
for select
to authenticated
using (
  user_id = auth.uid()
  or exists (
    select 1
    from public.tasks t
    join public.projects p on p.id = t.project_id
    where t.id = task_evidences.task_id and p.owner_id = auth.uid()
  )
);

create policy "task_evidences_insert_assignee"
on public.task_evidences
for insert
to authenticated
with check (
  user_id = auth.uid()
  and exists (
    select 1 from public.tasks t
    where t.id = task_evidences.task_id and t.assignee_id = auth.uid()
  )
);

create policy "task_evidences_delete_self"
on public.task_evidences
for delete
to authenticated
using (user_id = auth.uid());

insert into storage.buckets (id, name, public)
values ('task-evidences', 'task-evidences', false)
on conflict (id) do nothing;

create policy "storage_task_evidences_insert_own_prefix"
on storage.objects
for insert
to authenticated
with check (
  bucket_id = 'task-evidences'
  and (storage.foldername(name))[1] = auth.uid()::text
);

create policy "storage_task_evidences_select_own_prefix"
on storage.objects
for select
to authenticated
using (
  bucket_id = 'task-evidences'
  and (storage.foldername(name))[1] = auth.uid()::text
);
