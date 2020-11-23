CREATE TABLE "User" (
"id" INTEGER NOT NULL,
"email" varchar(255) NULL,
"user_type_id" INTEGER NULL,
"password" varchar(255) NULL,
"username" varchar(255) NULL,
"number" varchar(255) NULL,
"last_login" TIMESTAMP NULL,
"status" enum NULL,
"timestamp" TIMESTAMP NULL,
PRIMARY KEY ("id") 
);

CREATE TABLE "User_Type" (
"id" INTEGER NOT NULL,
"role" enum NULL,
PRIMARY KEY ("id") 
);

CREATE TABLE "Profile" (
"id" INTEGER NOT NULL,
"user_id" INTEGER NULL,
"first_name" VARCHAR(255) NULL,
"last_name" varchar NULL,
"title" VARCHAR(255) NULL,
"avatar_path" VARCHAR(255) NULL,
"street" VARCHAR(255) NULL,
"zip" VARCHAR(255) NULL,
"city" VARCHAR(255) NULL,
"country" enum NULL,
"state" VARCHAR(255) NULL,
"dob" DATE NULL,
"url_facebook" VARCHAR(255) NULL,
"url_microsoft" VARCHAR(255) NULL,
"timestamp" TIMESTAMP NULL,
PRIMARY KEY ("id") 
);

CREATE TABLE "Case" (
"id" INTEGER NOT NULL,
"profile_id" INTEGER NULL,
"name" VARCHAR(255) NULL,
"description" TinyText NULL,
"timestamp" TIMESTAMP NULL,
PRIMARY KEY ("id") 
);

CREATE TABLE "Report" (
);

CREATE TABLE "Invoice" (
);

CREATE TABLE "Event" (
"id" INTEGER NOT NULL,
"profile_id" INTEGER NULL,
"name" VARCHAR(255) NULL,
"descriptoin" VARCHAR(255) NULL,
"status" enum NULL,
"start_date" TIMESTAMP NULL,
"end_date" TIMESTAMP NULL,
"recursive_unit" enum NULL,
"recursive_period" INTEGER NULL,
"timestamp" TIMESTAMP NULL,
PRIMARY KEY ("id") 
);

CREATE TABLE "Invoices" (
"id" INTEGER NOT NULL,
"profile_id" INTEGER NULL,
"title" VARCHAR(255) NULL,
"amount" FLOAT NULL,
"currency" enum NULL,
"status" enum NULL,
"timestamp" TIMESTAMP NULL,
PRIMARY KEY ("id") 
);

CREATE TABLE "Message" (
"id" INTEGER NOT NULL,
"sender" INTEGER NULL,
"recipient" INTEGER NULL,
"content" VARCHAR(255) NULL,
"read" Boolean NULL,
"status" enum NULL,
"last_update" TIMESTAMP NULL,
"timestamp" TIMESTAMP NULL,
PRIMARY KEY ("id") 
);

CREATE TABLE "Post" (
"id" INTEGER NOT NULL,
"uesr_id" INTEGER NULL,
"activity_id" INTEGER NULL,
"missing_type" enum NULL,
"badged_award" enum NULL,
"duo_location" VARCHAR(255) NULL,
"is_draft" Boolean NULL,
"image_path" VARCHAR(255) NULL,
"fullname" VARCHAR(255) NULL,
"dob" VARCHAR(255) NULL,
"height" FLOAT NULL,
"weight" FLOAT NULL,
"hair" enum NULL,
"race" enum NULL,
"eye" enum NULL,
"medical condition" enum NULL,
"missing_since" TIMESTAMP NULL,
"missing_location_zip" VARCHAR(255) NULL,
"missing_location_street" VARCHAR(255) NULL,
"missing_location_city" VARCHAR(255) NULL,
"missing_location_country" enum NULL,
"missing_location_state" VARCHAR(255) NULL,
"circumstance" text NULL,
"email" VARCHAR(255) NULL,
"number" VARCHART NULL,
"verification_report_path" VARCHAR(255) NULL,
"verification_groupchat_link" VARCHAR(255) NULL,
"company_name" VARCHAR(255) NULL,
"company_image_path" VARCHAR(255) NULL,
"timestamp" DATE NULL,
PRIMARY KEY ("id") 
);

CREATE TABLE "Activity" (
"id" INTEGER NOT NULL,
"likes" VARCHAR(255) NULL,
"share" VARCHAR(255) NULL,
"likes_count" VARCHAR(255) NULL,
PRIMARY KEY ("id") 
);

CREATE TABLE "Likes" (
"id" INTEGER NOT NULL,
"active" Boolean NULL,
"activity_id" INTEGER NULL,
"user_id" INTEGER NULL,
"type" enum NULL,
"timestamp" TIMESTAMP NULL,
PRIMARY KEY ("id") 
);

CREATE TABLE "Comment" (
"id" INTEGER NOT NULL,
"activity_id" INTEGER NULL,
"text" VARCHAR(255) NULL,
"timestamp" TIMESTAMP NULL,
PRIMARY KEY ("id") 
);

CREATE TABLE "Follow" (
"id" INTEGER NOT NULL,
"follower_id" INTEGER NULL,
"followes_id" INTEGER NULL,
"timestamp" TIMESTAMP NULL,
PRIMARY KEY ("id") 
);


ALTER TABLE "User" ADD CONSTRAINT "fk_User_User_Type_1" FOREIGN KEY ("user_type_id") REFERENCES "User_Type" ("id");
ALTER TABLE "Profile" ADD CONSTRAINT "fk_Profile_User_1" FOREIGN KEY ("user_id") REFERENCES "User" ("id");
ALTER TABLE "Profile" ADD CONSTRAINT "fk_Profile_Case_1" FOREIGN KEY ("id") REFERENCES "Case" ("profile_id");
ALTER TABLE "Profile" ADD CONSTRAINT "fk_Profile_Event_1" FOREIGN KEY ("id") REFERENCES "Event" ("profile_id");
ALTER TABLE "Profile" ADD CONSTRAINT "fk_Profile_Invoices_1" FOREIGN KEY ("id") REFERENCES "Invoices" ("profile_id");
ALTER TABLE "Message" ADD CONSTRAINT "fk_Message_User_1" FOREIGN KEY ("sender") REFERENCES "User" ("id");
ALTER TABLE "Post" ADD CONSTRAINT "fk_Post_User_1" FOREIGN KEY ("uesr_id") REFERENCES "User" ("id");
ALTER TABLE "User" ADD CONSTRAINT "fk_User_Likes_1" FOREIGN KEY ("id") REFERENCES "Likes" ("user_id");
ALTER TABLE "Likes" ADD CONSTRAINT "fk_Likes_Activity_1" FOREIGN KEY ("id") REFERENCES "Activity" ("id");
ALTER TABLE "Activity" ADD CONSTRAINT "fk_Activity_Comment_1" FOREIGN KEY ("id") REFERENCES "Comment" ("activity_id");

