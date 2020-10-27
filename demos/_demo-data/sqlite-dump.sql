DROP TABLE IF EXISTS "login_user";
CREATE TABLE "login_user" (
  "id" integer PRIMARY KEY,
  "name" text,
  "email" text,
  "password" text,
  "role_id" integer
);

INSERT INTO "login_user" VALUES
    (1,'Standard User','user','$2y$10$BwEhcP8f15yOexf077VTHOnySn/mit49ZhpfeBkORQhrsmHr4U6Qy',1),
    (2,'Administrator','admin','$2y$10$p34ciRcg9GZyxukkLIaEnenGBao79fTFa4tFSrl7FvqrxnmEGlD4O',2);


DROP TABLE IF EXISTS "login_role";
CREATE TABLE "login_role" (
  "id" integer PRIMARY KEY,
  "name" text
);

INSERT INTO "login_role" VALUES (1,'User Role'),(2,'Admin Role');


DROP TABLE IF EXISTS "login_access_rule";
CREATE TABLE "login_access_rule" (
  "id" integer PRIMARY KEY,
  "role_id" integer,
  "model" text,
  "all_visible" integer,
  "visible_fields" text,
  "all_editable" integer,
  "editable_fields" text,
  "all_actions" integer,
  "actions" text,
  "conditions" text
);

INSERT INTO "login_access_rule" VALUES
    (1,2,'\\atk4\login\\Model\\User',1,null,1,null,1,null,null),
    (2,1,'\\atk4\login\\Model\\Role',1,null,0,null,1,null,null);
