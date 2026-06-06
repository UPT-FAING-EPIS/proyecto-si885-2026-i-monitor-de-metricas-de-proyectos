create table if not exists trello_connections (
  id bigserial primary key,
  user_id text not null unique,
  trello_member_id text null,
  token text not null,
  connected_at timestamptz not null default now(),
  last_sync_at timestamptz null,
  status text not null default 'connected',
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists idx_trello_connections_user_id on trello_connections(user_id);

create table if not exists trello_workspaces (
  id bigserial primary key,
  trello_workspace_id text not null unique,
  name text not null,
  description text null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists trello_boards (
  id bigserial primary key,
  trello_board_id text not null unique,
  workspace_id bigint not null references trello_workspaces(id) on delete cascade,
  name text not null,
  description text null,
  url text null,
  closed boolean not null default false,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists idx_trello_boards_workspace_id on trello_boards(workspace_id);

create table if not exists trello_lists (
  id bigserial primary key,
  trello_list_id text not null unique,
  board_id bigint not null references trello_boards(id) on delete cascade,
  name text not null,
  closed boolean not null default false,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists idx_trello_lists_board_id on trello_lists(board_id);

create table if not exists trello_cards (
  id bigserial primary key,
  trello_card_id text not null unique,
  list_id bigint not null references trello_lists(id) on delete cascade,
  board_id bigint not null references trello_boards(id) on delete cascade,
  name text not null,
  description text null,
  due_date timestamptz null,
  closed boolean not null default false,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists idx_trello_cards_board_id on trello_cards(board_id);
create index if not exists idx_trello_cards_list_id on trello_cards(list_id);
create index if not exists idx_trello_cards_due_date on trello_cards(due_date);

create table if not exists sync_logs (
  id bigserial primary key,
  user_id text not null,
  sync_type text not null,
  boards_processed integer not null default 0,
  lists_processed integer not null default 0,
  cards_processed integer not null default 0,
  errors_count integer not null default 0,
  started_at timestamptz not null default now(),
  finished_at timestamptz null
);

create index if not exists idx_sync_logs_user_id on sync_logs(user_id);
create index if not exists idx_sync_logs_started_at on sync_logs(started_at);

alter table sync_logs
  add column if not exists lists_processed integer not null default 0;
