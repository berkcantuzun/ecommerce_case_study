-- users
drop table if exists users cascade;
create table users
(
    id         serial primary key,
    name       varchar(100) not null,
    email      varchar(150) not null unique,
    password   varchar(255) not null,
    role       varchar(20)  not null default 'user',
    created_at timestamp    not null default now(),
    updated_at timestamp    not null default now()
);

-- categories
drop table if exists categories cascade;
create table categories
(
    id          serial primary key,
    name        varchar(100) not null,
    description text,
    created_at  timestamp    not null default now(),
    updated_at  timestamp    not null default now()
);

-- products
drop table if exists products cascade;
create table products
(
    id             serial primary key,
    name           varchar(100)   not null,
    description    text,
    price          numeric(10, 2) not null,
    stock_quantity int            not null,
    category_id    int references categories (id),
    created_at     timestamp      not null default now(),
    updated_at     timestamp      not null default now()
);

-- carts
drop table if exists carts cascade;
create table carts
(
    id         serial primary key,
    user_id    int references users (id),
    created_at timestamp not null default now(),
    updated_at timestamp not null default now()
);

-- cart_items
drop table if exists cart_items cascade;
create table cart_items
(
    id         serial primary key,
    cart_id    int references carts (id),
    product_id int references products (id),
    quantity   int       not null,
    created_at timestamp not null default now(),
    updated_at timestamp not null default now()
);

-- orders
drop table if exists orders cascade;
create table orders
(
    id           serial primary key,
    user_id      int references users (id),
    total_amount numeric(10, 2) not null,
    status       varchar(30)    not null default 'pending',
    created_at   timestamp      not null default now(),
    updated_at   timestamp      not null default now()
);

-- order_items
drop table if exists order_items cascade;
create table order_items
(
    id         serial primary key,
    order_id   int references orders (id),
    product_id int references products (id),
    quantity   int            not null,
    price      numeric(10, 2) not null,
    created_at timestamp      not null default now(),
    updated_at timestamp      not null default now()
);
