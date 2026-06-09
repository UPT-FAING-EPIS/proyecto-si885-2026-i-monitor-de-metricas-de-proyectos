alter table trello_workspaces add column if not exists user_id text not null default '';
alter table trello_boards add column if not exists user_id text not null default '';
alter table trello_lists add column if not exists user_id text not null default '';
alter table trello_cards add column if not exists user_id text not null default '';

alter table trello_workspaces drop constraint if exists trello_workspaces_trello_workspace_id_key;
alter table trello_boards drop constraint if exists trello_boards_trello_board_id_key;
alter table trello_lists drop constraint if exists trello_lists_trello_list_id_key;
alter table trello_cards drop constraint if exists trello_cards_trello_card_id_key;

create unique index if not exists uq_trello_workspaces_user_trello_id
  on trello_workspaces (user_id, trello_workspace_id);

create unique index if not exists uq_trello_boards_user_trello_id
  on trello_boards (user_id, trello_board_id);

create unique index if not exists uq_trello_lists_user_trello_id
  on trello_lists (user_id, trello_list_id);

create unique index if not exists uq_trello_cards_user_trello_id
  on trello_cards (user_id, trello_card_id);

create index if not exists idx_trello_workspaces_user_id on trello_workspaces (user_id);
create index if not exists idx_trello_boards_user_id on trello_boards (user_id);
create index if not exists idx_trello_lists_user_id on trello_lists (user_id);
create index if not exists idx_trello_cards_user_id on trello_cards (user_id);
