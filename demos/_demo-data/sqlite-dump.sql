DROP TABLE IF EXISTS "login_user";
CREATE TABLE "login_user" (
  "id" integer PRIMARY KEY,
  "name" text,
  "email" text,
  "password" text,
  "role_id" integer
);
