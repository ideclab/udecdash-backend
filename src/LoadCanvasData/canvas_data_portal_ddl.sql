CREATE TYPE course_dim_publicly_visible AS ENUM('False','True');
CREATE TYPE course_dim_workflow_state AS ENUM('completed', 'created', 'deleted', 'available', 'claimed');
CREATE TABLE IF NOT EXISTS course_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "root_account_id" BIGINT,
  "account_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "name" VARCHAR(256),
  "code" VARCHAR(256),
  "type" VARCHAR(256),
  "created_at" TIMESTAMP(3),
  "start_at" TIMESTAMP(3),
  "conclude_at" TIMESTAMP(3),
  "publicly_visible" course_dim_publicly_visible,
  "sis_source_id" VARCHAR(256),
  "workflow_state" course_dim_workflow_state,
  "wiki_id" BIGINT,
  "syllabus_body" TEXT
);
CREATE INDEX course_dim_root_account_id ON course_dim(root_account_id);
CREATE INDEX course_dim_account_id ON course_dim(account_id);
CREATE INDEX course_dim_enrollment_term_id ON course_dim(enrollment_term_id);
CREATE INDEX course_dim_sis_source_id ON course_dim(sis_source_id);
CREATE INDEX course_dim_workflow_state ON course_dim(workflow_state);
CREATE INDEX course_dim_wiki_id ON course_dim(wiki_id);

CREATE TYPE account_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TABLE IF NOT EXISTS account_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "name" VARCHAR(256),
  "depth" BIGINT,
  "workflow_state" account_dim_workflow_state,
  "parent_account" VARCHAR(256),
  "parent_account_id" BIGINT,
  "grandparent_account" VARCHAR(256),
  "grandparent_account_id" BIGINT,
  "root_account" VARCHAR(256),
  "root_account_id" BIGINT,
  "subaccount1" VARCHAR(256),
  "subaccount1_id" BIGINT,
  "subaccount2" VARCHAR(256),
  "subaccount2_id" BIGINT,
  "subaccount3" VARCHAR(256),
  "subaccount3_id" BIGINT,
  "subaccount4" VARCHAR(256),
  "subaccount4_id" BIGINT,
  "subaccount5" VARCHAR(256),
  "subaccount5_id" BIGINT,
  "subaccount6" VARCHAR(256),
  "subaccount6_id" BIGINT,
  "subaccount7" VARCHAR(256),
  "subaccount7_id" BIGINT,
  "subaccount8" VARCHAR(256),
  "subaccount8_id" BIGINT,
  "subaccount9" VARCHAR(256),
  "subaccount9_id" BIGINT,
  "subaccount10" VARCHAR(256),
  "subaccount10_id" BIGINT,
  "subaccount11" VARCHAR(256),
  "subaccount11_id" BIGINT,
  "subaccount12" VARCHAR(256),
  "subaccount12_id" BIGINT,
  "subaccount13" VARCHAR(256),
  "subaccount13_id" BIGINT,
  "subaccount14" VARCHAR(256),
  "subaccount14_id" BIGINT,
  "subaccount15" VARCHAR(256),
  "subaccount15_id" BIGINT,
  "sis_source_id" VARCHAR(256)
);
CREATE INDEX account_dim_workflow_state ON account_dim(workflow_state);
CREATE INDEX account_dim_parent_account_id ON account_dim(parent_account_id);
CREATE INDEX account_dim_grandparent_account_id ON account_dim(grandparent_account_id);
CREATE INDEX account_dim_root_account_id ON account_dim(root_account_id);
CREATE INDEX account_dim_subaccount1_id ON account_dim(subaccount1_id);
CREATE INDEX account_dim_subaccount2_id ON account_dim(subaccount2_id);
CREATE INDEX account_dim_subaccount3_id ON account_dim(subaccount3_id);
CREATE INDEX account_dim_subaccount4_id ON account_dim(subaccount4_id);
CREATE INDEX account_dim_subaccount5_id ON account_dim(subaccount5_id);
CREATE INDEX account_dim_subaccount6_id ON account_dim(subaccount6_id);
CREATE INDEX account_dim_subaccount7_id ON account_dim(subaccount7_id);
CREATE INDEX account_dim_subaccount8_id ON account_dim(subaccount8_id);
CREATE INDEX account_dim_subaccount9_id ON account_dim(subaccount9_id);
CREATE INDEX account_dim_subaccount10_id ON account_dim(subaccount10_id);
CREATE INDEX account_dim_subaccount11_id ON account_dim(subaccount11_id);
CREATE INDEX account_dim_subaccount12_id ON account_dim(subaccount12_id);
CREATE INDEX account_dim_subaccount13_id ON account_dim(subaccount13_id);
CREATE INDEX account_dim_subaccount14_id ON account_dim(subaccount14_id);
CREATE INDEX account_dim_subaccount15_id ON account_dim(subaccount15_id);
CREATE INDEX account_dim_sis_source_id ON account_dim(sis_source_id);

CREATE TYPE user_dim_workflow_state AS ENUM('creation_pending', 'deleted', 'pre_registered', 'registered');
CREATE TABLE IF NOT EXISTS user_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "root_account_id" BIGINT,
  "name" VARCHAR(256),
  "time_zone" VARCHAR(256),
  "created_at" TIMESTAMP(3),
  "visibility" VARCHAR(256),
  "school_name" VARCHAR(256),
  "school_position" VARCHAR(256),
  "gender" VARCHAR(256),
  "locale" VARCHAR(256),
  "public" VARCHAR(256),
  "birthdate" TIMESTAMP(3),
  "country_code" VARCHAR(256),
  "workflow_state" user_dim_workflow_state,
  "sortable_name" VARCHAR(256),
  "global_canvas_id" VARCHAR(256),
  "columna_prueba_1" VARCHAR(256),
  "columna_prueba_2" VARCHAR(256)
);
CREATE INDEX user_dim_root_account_id ON user_dim(root_account_id);
CREATE INDEX user_dim_workflow_state ON user_dim(workflow_state);
CREATE INDEX user_dim_global_canvas_id ON user_dim(global_canvas_id);

CREATE TYPE pseudonym_dim_password_auto_generated AS ENUM('False','True');
CREATE TYPE pseudonym_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TABLE IF NOT EXISTS pseudonym_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "user_id" BIGINT,
  "account_id" BIGINT,
  "workflow_state" pseudonym_dim_workflow_state,
  "last_request_at" TIMESTAMP(3),
  "last_login_at" TIMESTAMP(3),
  "current_login_at" TIMESTAMP(3),
  "last_login_ip" VARCHAR(256),
  "current_login_ip" VARCHAR(256),
  "position" BIGINT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "password_auto_generated" pseudonym_dim_password_auto_generated,
  "deleted_at" TIMESTAMP(3),
  "sis_user_id" VARCHAR(256),
  "unique_name" VARCHAR(256),
  "integration_id" VARCHAR(256),
  "authentication_provider_id" BIGINT
);
CREATE INDEX pseudonym_dim_account_id ON pseudonym_dim(account_id);
CREATE INDEX pseudonym_dim_workflow_state ON pseudonym_dim(workflow_state);
CREATE INDEX pseudonym_dim_sis_user_id ON pseudonym_dim(sis_user_id);
CREATE INDEX pseudonym_dim_integration_id ON pseudonym_dim(integration_id);
CREATE INDEX pseudonym_dim_authentication_provider_id ON pseudonym_dim(authentication_provider_id);

CREATE TABLE IF NOT EXISTS pseudonym_fact (
  "pseudonym_id" BIGINT PRIMARY KEY,
  "user_id" BIGINT,
  "account_id" BIGINT,
  "login_count" BIGINT,
  "failed_login_count" BIGINT
);
CREATE INDEX pseudonym_fact_user_id ON pseudonym_fact(user_id);
CREATE INDEX pseudonym_fact_account_id ON pseudonym_fact(account_id);

CREATE TYPE assignment_dim_workflow_state AS ENUM('unpublished', 'published', 'deleted', 'failed_to_duplicate');
CREATE TYPE assignment_dim_peer_reviews_assigned AS ENUM('False','True');
CREATE TYPE assignment_dim_peer_reviews AS ENUM('False','True');
CREATE TYPE assignment_dim_automatic_peer_reviews AS ENUM('False','True');
CREATE TYPE assignment_dim_all_day AS ENUM('False','True');
CREATE TYPE assignment_dim_could_be_locked AS ENUM('False','True');
CREATE TYPE assignment_dim_grade_group_students_individually AS ENUM('False','True');
CREATE TYPE assignment_dim_anonymous_peer_reviews AS ENUM('False','True');
CREATE TYPE assignment_dim_muted AS ENUM('False','True');
CREATE TYPE assignment_dim_visibility AS ENUM('everyone', 'only_visible_to_overrides');
CREATE TABLE IF NOT EXISTS assignment_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "course_id" BIGINT,
  "title" VARCHAR(256),
  "description" TEXT,
  "due_at" TIMESTAMP(3),
  "unlock_at" TIMESTAMP(3),
  "lock_at" TIMESTAMP(3),
  "points_possible" DOUBLE PRECISION,
  "grading_type" VARCHAR(256),
  "submission_types" VARCHAR(256),
  "workflow_state" assignment_dim_workflow_state,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "peer_review_count" BIGINT,
  "peer_reviews_due_at" TIMESTAMP(3),
  "peer_reviews_assigned" assignment_dim_peer_reviews_assigned,
  "peer_reviews" assignment_dim_peer_reviews,
  "automatic_peer_reviews" assignment_dim_automatic_peer_reviews,
  "all_day" assignment_dim_all_day,
  "all_day_date" DATE,
  "could_be_locked" assignment_dim_could_be_locked,
  "grade_group_students_individually" assignment_dim_grade_group_students_individually,
  "anonymous_peer_reviews" assignment_dim_anonymous_peer_reviews,
  "muted" assignment_dim_muted,
  "assignment_group_id" BIGINT,
  "position" BIGINT,
  "visibility" assignment_dim_visibility,
  "external_tool_id" BIGINT
);
CREATE INDEX assignment_dim_course_id ON  assignment_dim(course_id);
CREATE INDEX assignment_dim_workflow_state ON  assignment_dim(workflow_state);
CREATE INDEX assignment_dim_assignment_group_id ON  assignment_dim(assignment_group_id);
CREATE INDEX assignment_dim_external_tool_id ON  assignment_dim(external_tool_id);

CREATE TABLE IF NOT EXISTS assignment_fact (
  "assignment_id" BIGINT PRIMARY KEY,
  "course_id" BIGINT,
  "course_account_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "points_possible" DOUBLE PRECISION,
  "peer_review_count" BIGINT,
  "assignment_group_id" BIGINT,
  "external_tool_id" BIGINT
);
CREATE INDEX assignment_fact_course_id ON assignment_fact(course_id);
CREATE INDEX assignment_fact_course_account_id ON assignment_fact(course_account_id);
CREATE INDEX assignment_fact_enrollment_term_id ON assignment_fact(enrollment_term_id);
CREATE INDEX assignment_fact_assignment_group_id ON assignment_fact(assignment_group_id);
CREATE INDEX assignment_fact_external_tool_id ON assignment_fact(external_tool_id);

CREATE TABLE IF NOT EXISTS assignment_rule_dim (
  "assignment_id" BIGINT PRIMARY KEY,
  "drop_rule" VARCHAR(256)
);

CREATE TYPE submission_dim_submission_type AS ENUM('discussion_topic', 'external_tool', 'media_recording', 'online_file_upload', 'online_quiz', 'online_text_entry', 'online_upload', 'online_url','basic_lti_launch');
CREATE TYPE submission_dim_workflow_state AS ENUM('graded', 'pending_review', 'submitted', 'unsubmitted','deleted');
CREATE TYPE submission_dim_processed AS ENUM('false','true');
CREATE TYPE submission_dim_grade_matches_current_submission AS ENUM('false','true');
CREATE TYPE submission_dim_has_rubric_assessment AS ENUM('false','true');
CREATE TYPE submission_dim_has_admin_comment AS ENUM('False','True');
CREATE TYPE submission_dim_excused AS ENUM('excused_submission', 'regular_submission');
CREATE TYPE submission_dim_graded_anonymously AS ENUM('graded_anonymously', 'not_graded_anonymously');
CREATE TYPE submission_dim_grade_state AS ENUM('auto_graded', 'human_graded', 'not_graded');
CREATE TABLE IF NOT EXISTS submission_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "body" TEXT,
  "url" VARCHAR(256),
  "grade" VARCHAR(256),
  "submitted_at" TIMESTAMP(3),
  "submission_type" submission_dim_submission_type,
  "workflow_state" submission_dim_workflow_state,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "processed" submission_dim_processed,
  "process_attempts" BIGINT,
  "grade_matches_current_submission" submission_dim_grade_matches_current_submission,
  "published_grade" VARCHAR(256),
  "graded_at" TIMESTAMP(3),
  "has_rubric_assessment" submission_dim_has_rubric_assessment,
  "attempt" BIGINT,
  "has_admin_comment" submission_dim_has_admin_comment,
  "assignment_id" BIGINT,
  "excused" submission_dim_excused,
  "graded_anonymously" submission_dim_graded_anonymously,
  "grader_id" BIGINT,
  "group_id" BIGINT,
  "quiz_submission_id" BIGINT,
  "user_id" BIGINT,
  "grade_state" submission_dim_grade_state,
  "posted_at" TIMESTAMP(3)
);
CREATE INDEX submission_dim_workflow_state ON submission_dim(workflow_state);
CREATE INDEX submission_dim_assignment_id ON submission_dim(assignment_id);
CREATE INDEX submission_dim_grader_id ON submission_dim(grader_id);
CREATE INDEX submission_dim_group_id ON submission_dim(group_id);
CREATE INDEX submission_dim_quiz_submission_id ON submission_dim(quiz_submission_id);
CREATE INDEX submission_dim_user_id ON submission_dim(user_id);

CREATE TABLE IF NOT EXISTS submission_fact (
  "submission_id" BIGINT PRIMARY KEY,
  "assignment_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "user_id" BIGINT,
  "grader_id" BIGINT,
  "course_account_id" BIGINT,
  "enrollment_rollup_id" BIGINT,
  "score" DOUBLE PRECISION,
  "published_score" DOUBLE PRECISION,
  "what_if_score" DOUBLE PRECISION,
  "submission_comments_count" BIGINT,
  "account_id" BIGINT,
  "assignment_group_id" BIGINT,
  "group_id" BIGINT,
  "quiz_id" BIGINT,
  "quiz_submission_id" BIGINT,
  "wiki_id" BIGINT
);
CREATE INDEX submission_fact_assignment_id ON submission_fact(assignment_id);
CREATE INDEX submission_fact_course_id ON submission_fact(course_id);
CREATE INDEX submission_fact_enrollment_term_id ON submission_fact(enrollment_term_id);
CREATE INDEX submission_fact_user_id ON submission_fact(user_id);
CREATE INDEX submission_fact_grader_id ON submission_fact(grader_id);
CREATE INDEX submission_fact_course_account_id ON submission_fact(course_account_id);
CREATE INDEX submission_fact_enrollment_rollup_id ON submission_fact(enrollment_rollup_id);
CREATE INDEX submission_fact_account_id ON submission_fact(account_id);
CREATE INDEX submission_fact_assignment_group_id ON submission_fact(assignment_group_id);
CREATE INDEX submission_fact_group_id ON submission_fact(group_id);
CREATE INDEX submission_fact_quiz_id ON submission_fact(quiz_id);
CREATE INDEX submission_fact_quiz_submission_id ON submission_fact(quiz_submission_id);
CREATE INDEX submission_fact_wiki_id ON submission_fact(wiki_id);

CREATE TABLE IF NOT EXISTS submission_comment_fact (
  "submission_comment_id" BIGINT PRIMARY KEY,
  "submission_id" BIGINT,
  "recipient_id" BIGINT,
  "author_id" BIGINT,
  "assignment_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "course_account_id" BIGINT,
  "message_size_bytes" BIGINT,
  "message_character_count" BIGINT,
  "message_word_count" BIGINT,
  message_line_count BIGINT
);
CREATE INDEX submission_comment_fact_submission_id ON submission_comment_fact(submission_id);
CREATE INDEX submission_comment_fact_recipient_id ON submission_comment_fact(recipient_id);
CREATE INDEX submission_comment_fact_author_id ON submission_comment_fact(author_id);
CREATE INDEX submission_comment_fact_assignment_id ON submission_comment_fact(assignment_id);
CREATE INDEX submission_comment_fact_course_id ON submission_comment_fact(course_id);
CREATE INDEX submission_comment_fact_enrollment_term_id ON submission_comment_fact(enrollment_term_id);
CREATE INDEX submission_comment_fact_course_account_id ON submission_comment_fact(course_account_id);

CREATE TYPE submission_comment_dim_anonymous AS ENUM('False','True');
CREATE TYPE submission_comment_dim_teacher_only_comment AS ENUM('False','True');
CREATE TYPE submission_comment_dim_hidden AS ENUM('False','True');
CREATE TABLE IF NOT EXISTS submission_comment_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "submission_id" BIGINT,
  "recipient_id" BIGINT,
  "author_id" BIGINT,
  "assessment_request_id" BIGINT,
  "group_comment_id" VARCHAR(256),
  "comment" TEXT,
  "author_name" VARCHAR(256),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "anonymous" submission_comment_dim_anonymous,
  "teacher_only_comment" submission_comment_dim_teacher_only_comment,
  "hidden" submission_comment_dim_hidden
);
CREATE INDEX submission_comment_dim_submission_id ON submission_comment_dim(submission_id);
CREATE INDEX submission_comment_dim_recipient_id ON submission_comment_dim(recipient_id);
CREATE INDEX submission_comment_dim_author_id ON submission_comment_dim(author_id);
CREATE INDEX submission_comment_dim_assessment_request_id ON submission_comment_dim(assessment_request_id);
CREATE INDEX submission_comment_dim_group_comment_id ON submission_comment_dim(group_comment_id);

CREATE TYPE assignment_group_dim_workflow_state AS ENUM('available', 'deleted');
CREATE TABLE IF NOT EXISTS assignment_group_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "course_id" BIGINT,
  "name" VARCHAR(256),
  "default_assignment_name" VARCHAR(256),
  "workflow_state" assignment_group_dim_workflow_state,
  "position" BIGINT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX assignment_group_dim_course_id ON assignment_group_dim(course_id);
CREATE INDEX assignment_group_dim_workflow_state ON assignment_group_dim(workflow_state);

CREATE TABLE IF NOT EXISTS assignment_group_fact (
  "assignment_group_id" BIGINT PRIMARY KEY,
  "course_id" BIGINT,
  "group_weight" DOUBLE PRECISION
);
CREATE INDEX assignment_group_fact_course_id ON assignment_group_fact(course_id);

CREATE TABLE IF NOT EXISTS assignment_group_rule_dim (
  "assignment_group_id" BIGINT PRIMARY KEY,
  "drop_lowest" BIGINT,
  "drop_highest" BIGINT
);

CREATE TABLE IF NOT EXISTS assignment_override_user_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "assignment_id" BIGINT,
  "assignment_override_id" BIGINT,
  "quiz_id" BIGINT,
  "user_id" BIGINT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX assignment_override_user_dim_assignment_id ON assignment_override_user_dim(assignment_id);
CREATE INDEX assignment_override_user_dim_assignment_override_id ON assignment_override_user_dim(assignment_override_id);
CREATE INDEX assignment_override_user_dim_quiz_id ON assignment_override_user_dim(quiz_id);
CREATE INDEX assignment_override_user_dim_user_id ON assignment_override_user_dim(user_id);

CREATE TABLE IF NOT EXISTS assignment_override_user_fact (
  "assignment_override_user_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "assignment_group_id" BIGINT,
  "assignment_id" BIGINT,
  "assignment_override_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "quiz_id" BIGINT,
  "user_id" BIGINT
);
CREATE INDEX assignment_override_user_fact_account_id ON assignment_override_user_fact(account_id);
CREATE INDEX assignment_override_user_fact_assignment_group_id ON assignment_override_user_fact(assignment_group_id);
CREATE INDEX assignment_override_user_fact_assignment_id ON assignment_override_user_fact(assignment_id);
CREATE INDEX assignment_override_user_fact_assignment_override_id ON assignment_override_user_fact(assignment_override_id);
CREATE INDEX assignment_override_user_fact_course_id ON assignment_override_user_fact(course_id);
CREATE INDEX assignment_override_user_fact_enrollment_term_id ON assignment_override_user_fact(enrollment_term_id);
CREATE INDEX assignment_override_user_fact_quiz_id ON assignment_override_user_fact(quiz_id);
CREATE INDEX assignment_override_user_fact_user_id ON assignment_override_user_fact(user_id);

CREATE TYPE assignment_override_dim_all_day AS ENUM('new_all_day', 'same_all_day');
CREATE TYPE assignment_override_dim_due_at_overridden AS ENUM('new_due_at', 'same_due_at');
CREATE TYPE assignment_override_dim_lock_at_overridden AS ENUM('new_lock_at', 'same_lock_at');
CREATE TYPE assignment_override_dim_set_type AS ENUM('course_section', 'group', 'adhoc');
CREATE TYPE assignment_override_dim_unlock_at_overridden AS ENUM('new_unlock_at', 'same_unlock_at');
CREATE TYPE assignment_override_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TABLE IF NOT EXISTS assignment_override_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "assignment_id" BIGINT,
  "course_section_id" BIGINT,
  "group_id" BIGINT,
  "quiz_id" BIGINT,
  "all_day" assignment_override_dim_all_day,
  "all_day_date" DATE,
  "assignment_version" BIGINT,
  "created_at" TIMESTAMP(3),
  "due_at" TIMESTAMP(3),
  "due_at_overridden" assignment_override_dim_due_at_overridden,
  "lock_at" TIMESTAMP(3),
  "lock_at_overridden" assignment_override_dim_lock_at_overridden,
  "set_type" assignment_override_dim_set_type,
  "title" TEXT,
  "unlock_at" TIMESTAMP(3),
  "unlock_at_overridden" assignment_override_dim_unlock_at_overridden,
  "updated_at" TIMESTAMP(3),
  "quiz_version" BIGINT,
  "workflow_state" assignment_override_dim_workflow_state
);
CREATE INDEX assignment_override_dim_assignment_id ON assignment_override_dim(assignment_id);
CREATE INDEX assignment_override_dim_course_section_id ON assignment_override_dim(course_section_id);
CREATE INDEX assignment_override_dim_group_id ON assignment_override_dim(group_id);
CREATE INDEX assignment_override_dim_quiz_id ON assignment_override_dim(quiz_id);
CREATE INDEX assignment_override_dim_workflow_state ON assignment_override_dim(workflow_state);

CREATE TABLE IF NOT EXISTS assignment_override_fact (
  "assignment_override_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "assignment_id" BIGINT,
  "assignment_group_id" BIGINT,
  "course_id" BIGINT,
  "course_section_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "group_id" BIGINT,
  "group_category_id" BIGINT,
  "group_parent_account_id" BIGINT,
  "nonxlist_course_id" BIGINT,
  "quiz_id" BIGINT,
  "group_wiki_id" BIGINT
);
CREATE INDEX assignment_override_fact_account_id ON assignment_override_fact(account_id);
CREATE INDEX assignment_override_fact_assignment_id ON assignment_override_fact(assignment_id);
CREATE INDEX assignment_override_fact_assignment_group_id ON assignment_override_fact(assignment_group_id);
CREATE INDEX assignment_override_fact_course_id ON assignment_override_fact(course_id);
CREATE INDEX assignment_override_fact_course_section_id ON assignment_override_fact(course_section_id);
CREATE INDEX assignment_override_fact_enrollment_term_id ON assignment_override_fact(enrollment_term_id);
CREATE INDEX assignment_override_fact_group_id ON assignment_override_fact(group_id);
CREATE INDEX assignment_override_fact_group_category_id ON assignment_override_fact(group_category_id);
CREATE INDEX assignment_override_fact_group_parent_account_id ON assignment_override_fact(group_parent_account_id);
CREATE INDEX assignment_override_fact_nonxlist_course_id ON assignment_override_fact(nonxlist_course_id);
CREATE INDEX assignment_override_fact_quiz_id ON assignment_override_fact(quiz_id);
CREATE INDEX assignment_override_fact_group_wiki_id ON assignment_override_fact(group_wiki_id);

CREATE TABLE IF NOT EXISTS assignment_override_user_rollup_fact (
  "assignment_id" BIGINT,
  "assignment_override_id" BIGINT,
  "assignment_override_user_adhoc_id" BIGINT,
  "assignment_group_id" BIGINT,
  "course_id" BIGINT,
  "course_account_id" BIGINT,
  "course_section_id" BIGINT,
  "enrollment_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "group_category_id" BIGINT,
  "group_id" BIGINT,
  "group_parent_account_id" BIGINT,
  "group_wiki_id" BIGINT,
  "nonxlist_course_id" BIGINT,
  "quiz_id" BIGINT,
  "user_id" BIGINT
);
CREATE INDEX aour_fact_assignment_id ON assignment_override_user_rollup_fact(assignment_id);
CREATE INDEX aour_fact_assignment_override_id ON assignment_override_user_rollup_fact(assignment_override_id);
CREATE INDEX aour_fact_assignment_override_user_adhoc_id ON assignment_override_user_rollup_fact(assignment_override_user_adhoc_id);
CREATE INDEX aour_fact_assignment_group_id ON assignment_override_user_rollup_fact(assignment_group_id);
CREATE INDEX aour_fact_course_id ON assignment_override_user_rollup_fact(course_id);
CREATE INDEX aour_fact_course_account_id ON assignment_override_user_rollup_fact(course_account_id);
CREATE INDEX aour_fact_course_section_id ON assignment_override_user_rollup_fact(course_section_id);
CREATE INDEX aour_fact_enrollment_id ON assignment_override_user_rollup_fact(enrollment_id);
CREATE INDEX aour_fact_enrollment_term_id ON assignment_override_user_rollup_fact(enrollment_term_id);
CREATE INDEX aour_fact_group_category_id ON assignment_override_user_rollup_fact(group_category_id);
CREATE INDEX aour_fact_group_id ON assignment_override_user_rollup_fact(group_id);
CREATE INDEX aour_fact_group_parent_account_id ON assignment_override_user_rollup_fact(group_parent_account_id);
CREATE INDEX aour_fact_group_wiki_id ON assignment_override_user_rollup_fact(group_wiki_id);
CREATE INDEX aour_fact_nonxlist_course_id ON assignment_override_user_rollup_fact(nonxlist_course_id);
CREATE INDEX aour_fact_quiz_id ON assignment_override_user_rollup_fact(quiz_id);
CREATE INDEX aour_fact_user_id ON assignment_override_user_rollup_fact(user_id);

CREATE TABLE IF NOT EXISTS submission_file_fact (
  "submission_file_id" BIGINT PRIMARY KEY,
  "file_id" BIGINT,
  "submission_id" BIGINT,
  "assignment_id" BIGINT,
  "user_id" BIGINT,
  "grader_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT
);
CREATE INDEX submission_file_fact_file_id ON submission_file_fact(file_id);
CREATE INDEX submission_file_fact_submission_id ON submission_file_fact(submission_id);
CREATE INDEX submission_file_fact_assignment_id ON submission_file_fact(assignment_id);
CREATE INDEX submission_file_fact_user_id ON submission_file_fact(user_id);
CREATE INDEX submission_file_fact_grader_id ON submission_file_fact(grader_id);
CREATE INDEX submission_file_fact_course_id ON submission_file_fact(course_id);
CREATE INDEX submission_file_fact_enrollment_term_id ON submission_file_fact(enrollment_term_id);

CREATE TABLE IF NOT EXISTS catalog_dim (
  "id" BIGINT PRIMARY KEY,
  "parent_id" BIGINT,
  "name" VARCHAR(256),
  "currency" VARCHAR(256),
  "country" VARCHAR(256),
  "time_zone" VARCHAR(256),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX catalog_dim_parent_id ON catalog_dim(parent_id);

CREATE TYPE catalog_product_dim_enrollment_open AS ENUM('False','True');
CREATE TYPE catalog_product_dim_has_waitlist AS ENUM('False','True');
CREATE TABLE IF NOT EXISTS catalog_product_dim (
  "id" BIGINT PRIMARY KEY,
  "catalog_id" BIGINT,
  "start_date" TIMESTAMP(3),
  "end_date" TIMESTAMP(3),
  "course_id" BIGINT,
  "product_type" VARCHAR(256),
  "title" VARCHAR(256),
  "visibility" VARCHAR(256),
  "enrollment_open" catalog_product_dim_enrollment_open,
  "has_waitlist" catalog_product_dim_has_waitlist,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX catalog_product_dim_catalog_id ON catalog_product_dim(catalog_id);
CREATE INDEX catalog_product_dim_course_id ON catalog_product_dim(course_id);

CREATE TABLE IF NOT EXISTS catalog_product_fact (
  "catalog_product_id" BIGINT PRIMARY KEY,
  "catalog_id" BIGINT,
  "parent_catalog_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "enrollment_fee" DOUBLE PRECISION,
  "credits" DOUBLE PRECISION,
  "waitlist_capacity" BIGINT,
  "enrollment_capacity" BIGINT
);
CREATE INDEX catalog_product_fact_catalog_id ON catalog_product_fact(catalog_id);
CREATE INDEX catalog_product_fact_parent_catalog_id ON catalog_product_fact(parent_catalog_id);
CREATE INDEX catalog_product_fact_course_id ON catalog_product_fact(course_id);
CREATE INDEX catalog_product_fact_enrollment_term_id ON catalog_product_fact(enrollment_term_id);

CREATE TABLE IF NOT EXISTS catalog_enrollment_dim (
  "id" BIGINT PRIMARY KEY,
  "catalog_product_id" BIGINT,
  "user_id" BIGINT,
  "root_program_id" BIGINT,
  "status" VARCHAR(256),
  "requirements_completed_at" TIMESTAMP(3),
  "ends_at" TIMESTAMP(3),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX catalog_enrollment_dim_catalog_product_id ON catalog_enrollment_dim(catalog_product_id);
CREATE INDEX catalog_enrollment_dim_user_id ON catalog_enrollment_dim(user_id);
CREATE INDEX catalog_enrollment_dim_root_program_id ON catalog_enrollment_dim(root_program_id);

CREATE TABLE IF NOT EXISTS catalog_user_registration_dim (
  "id" BIGINT PRIMARY KEY,
  "catalog_id" BIGINT,
  "user_id" BIGINT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX catalog_user_registration_dim_catalog_id ON catalog_user_registration_dim(catalog_id);
CREATE INDEX catalog_user_registration_dim_user_id ON catalog_user_registration_dim(user_id);

CREATE TABLE IF NOT EXISTS catalog_program_requirement_fact (
  "id" BIGINT PRIMARY KEY,
  "catalog_id" BIGINT,
  "catalog_program_id" BIGINT,
  "catalog_product_id" BIGINT,
  "course_id" BIGINT
);
CREATE INDEX catalog_program_requirement_fact_catalog_id ON catalog_program_requirement_fact(catalog_id);
CREATE INDEX catalog_program_requirement_fact_catalog_program_id ON catalog_program_requirement_fact(catalog_program_id);
CREATE INDEX catalog_program_requirement_fact_catalog_product_id ON catalog_program_requirement_fact(catalog_product_id);
CREATE INDEX catalog_program_requirement_fact_course_id ON catalog_program_requirement_fact(course_id);

CREATE TYPE communication_channel_dim_type AS ENUM('email', 'facebook', 'push', 'sms', 'twitter');
CREATE TYPE communication_channel_dim_workflow_state AS ENUM('unconfirmed', 'active','retired');
CREATE TABLE IF NOT EXISTS communication_channel_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "user_id" BIGINT,
  "address" VARCHAR(256),
  "type" communication_channel_dim_type,
  "position" BIGINT,
  "workflow_state" communication_channel_dim_workflow_state,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX communication_channel_dim_user_id ON communication_channel_dim(user_id);
CREATE INDEX communication_channel_dim_workflow_state ON communication_channel_dim(workflow_state);

CREATE TABLE IF NOT EXISTS communication_channel_fact (
  "communication_channel_id" BIGINT PRIMARY KEY,
  "user_id" BIGINT,
  "bounce_count" BIGINT
);
CREATE INDEX communication_channel_fact_user_id ON communication_channel_fact(user_id);

CREATE TABLE IF NOT EXISTS conference_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "title" VARCHAR(256),
  "conference_type" VARCHAR(256),
  "group_id" BIGINT,
  "course_id" BIGINT,
  "account_id" BIGINT,
  "start_at" TIMESTAMP(3),
  "started_at" TIMESTAMP(3),
  "end_at" TIMESTAMP(3),
  "ended_at" TIMESTAMP(3),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX conference_dim_group_id ON conference_dim(group_id);
CREATE INDEX conference_dim_course_id ON conference_dim(course_id);
CREATE INDEX conference_dim_account_id ON conference_dim(account_id);

CREATE TABLE IF NOT EXISTS conference_fact (
  "conference_id" BIGINT PRIMARY KEY,
  "group_id" BIGINT,
  "course_id" BIGINT,
  "account_id" BIGINT,
  "duration" DOUBLE PRECISION
);
CREATE INDEX conference_fact_group_id ON conference_fact(group_id);
CREATE INDEX conference_fact_course_id ON conference_fact(course_id);
CREATE INDEX conference_fact_account_id ON conference_fact(account_id);

CREATE TABLE IF NOT EXISTS conference_participant_dim (
  "id" BIGINT PRIMARY KEY,
  "user_id" BIGINT,
  "conference_id" BIGINT,
  "participation_type" VARCHAR(256),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX conference_participant_dim_user_id ON conference_participant_dim(user_id);
CREATE INDEX conference_participant_dim_conference_id ON conference_participant_dim(conference_id);

CREATE TABLE IF NOT EXISTS conference_participant_fact (
  "conference_participant_id" BIGINT PRIMARY KEY,
  "group_id" BIGINT,
  "course_id" BIGINT,
  "account_id" BIGINT,
  "user_id" BIGINT,
  "conference_id" BIGINT
);
CREATE INDEX conference_participant_fact_group_id ON conference_participant_fact(group_id);
CREATE INDEX conference_participant_fact_course_id ON conference_participant_fact(course_id);
CREATE INDEX conference_participant_fact_account_id ON conference_participant_fact(account_id);
CREATE INDEX conference_participant_fact_user_id ON conference_participant_fact(user_id);
CREATE INDEX conference_participant_fact_conference_id ON conference_participant_fact(conference_id);

CREATE TYPE conversation_dim_has_attachments AS ENUM('False','True');
CREATE TYPE conversation_dim_has_media_objects AS ENUM('False','True');
CREATE TABLE IF NOT EXISTS conversation_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "has_attachments" conversation_dim_has_attachments,
  "has_media_objects" conversation_dim_has_media_objects,
  "subject" VARCHAR(256),
  "course_id" BIGINT,
  "group_id" BIGINT,
  "account_id" BIGINT
);
CREATE INDEX conversation_dim_course_id ON conversation_dim(course_id);
CREATE INDEX conversation_dim_group_id ON conversation_dim(group_id);
CREATE INDEX conversation_dim_account_id ON conversation_dim(account_id);

CREATE TYPE conversation_message_dim_generated AS ENUM('False','True');
CREATE TYPE conversation_message_dim_has_attachments AS ENUM('False','True');
CREATE TYPE conversation_message_dim_has_media_objects AS ENUM('False','True');
CREATE TABLE IF NOT EXISTS conversation_message_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "conversation_id" BIGINT,
  "author_id" BIGINT,
  "created_at" TIMESTAMP(3),
  "generated" conversation_message_dim_generated,
  "has_attachments" conversation_message_dim_has_attachments,
  "has_media_objects" conversation_message_dim_has_media_objects,
  "body" TEXT
);
CREATE INDEX conversation_message_dim_conversation_id ON conversation_message_dim(conversation_id);
CREATE INDEX conversation_message_dim_author_id ON conversation_message_dim(author_id);

CREATE TABLE IF NOT EXISTS conversation_message_participant_fact (
  "conversation_message_id" BIGINT,
  "conversation_id" BIGINT,
  "user_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "course_account_id" BIGINT,
  "group_id" BIGINT,
  "account_id" BIGINT,
  "enrollment_rollup_id" BIGINT,
  "message_size_bytes" BIGINT,
  "message_character_count" BIGINT,
  "message_word_count" BIGINT,
  "message_line_count" BIGINT,
  PRIMARY KEY (conversation_message_id, user_id)
);
CREATE INDEX conversation_message_participant_fact_conversation_message_id ON conversation_message_participant_fact(conversation_message_id);
CREATE INDEX conversation_message_participant_fact_conversation_id ON conversation_message_participant_fact(conversation_id);
CREATE INDEX conversation_message_participant_fact_user_id ON conversation_message_participant_fact(user_id);
CREATE INDEX conversation_message_participant_fact_course_id ON conversation_message_participant_fact(course_id);
CREATE INDEX conversation_message_participant_fact_enrollment_term_id ON conversation_message_participant_fact(enrollment_term_id);
CREATE INDEX conversation_message_participant_fact_course_account_id ON conversation_message_participant_fact(course_account_id);
CREATE INDEX conversation_message_participant_fact_group_id ON conversation_message_participant_fact(group_id);
CREATE INDEX conversation_message_participant_fact_account_id ON conversation_message_participant_fact(account_id);
CREATE INDEX conversation_message_participant_fact_enrollment_rollup_id ON conversation_message_participant_fact(enrollment_rollup_id);

CREATE TYPE discussion_topic_dim_workflow_state AS ENUM('unpublished', 'active', 'locked', 'deleted', 'post_delayed');
CREATE TYPE discussion_topic_dim_pinned AS ENUM('False','True');
CREATE TYPE discussion_topic_dim_locked AS ENUM('False','True');
CREATE TABLE IF NOT EXISTS discussion_topic_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "title" VARCHAR(256),
  "message" TEXT,
  "type" VARCHAR(256),
  "workflow_state" discussion_topic_dim_workflow_state,
  "last_reply_at" TIMESTAMP(3),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "delayed_post_at" TIMESTAMP(3),
  "posted_at" TIMESTAMP(3),
  "deleted_at" TIMESTAMP(3),
  "discussion_type" VARCHAR(256),
  "pinned" discussion_topic_dim_pinned,
  "locked" discussion_topic_dim_locked,
  "course_id" BIGINT,
  "group_id" BIGINT
);
CREATE INDEX discussion_topic_dim_workflow_state ON discussion_topic_dim(workflow_state);
CREATE INDEX discussion_topic_dim_course_id ON discussion_topic_dim(course_id);
CREATE INDEX discussion_topic_dim_group_id ON discussion_topic_dim(group_id);

CREATE TABLE IF NOT EXISTS discussion_topic_fact (
  "discussion_topic_id" BIGINT PRIMARY KEY,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "course_account_id" BIGINT,
  "user_id" BIGINT,
  "assignment_id" BIGINT,
  "editor_id" BIGINT,
  "enrollment_rollup_id" BIGINT,
  "message_length" BIGINT,
  "group_id" BIGINT,
  "group_parent_course_id" BIGINT,
  "group_parent_account_id" BIGINT,
  "group_parent_course_account_id" BIGINT
);
CREATE INDEX discussion_topic_fact_course_id ON discussion_topic_fact(course_id);
CREATE INDEX discussion_topic_fact_enrollment_term_id ON discussion_topic_fact(enrollment_term_id);
CREATE INDEX discussion_topic_fact_course_account_id ON discussion_topic_fact(course_account_id);
CREATE INDEX discussion_topic_fact_user_id ON discussion_topic_fact(user_id);
CREATE INDEX discussion_topic_fact_assignment_id ON discussion_topic_fact(assignment_id);
CREATE INDEX discussion_topic_fact_editor_id ON discussion_topic_fact(editor_id);
CREATE INDEX discussion_topic_fact_enrollment_rollup_id ON discussion_topic_fact(enrollment_rollup_id);
CREATE INDEX discussion_topic_fact_group_id ON discussion_topic_fact(group_id);
CREATE INDEX discussion_topic_fact_group_parent_course_id ON discussion_topic_fact(group_parent_course_id);
CREATE INDEX discussion_topic_fact_group_parent_account_id ON discussion_topic_fact(group_parent_account_id);
CREATE INDEX discussion_topic_fact_group_parent_course_account_id ON discussion_topic_fact(group_parent_course_account_id);

CREATE TYPE discussion_entry_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TABLE IF NOT EXISTS discussion_entry_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "message" TEXT,
  "workflow_state" discussion_entry_dim_workflow_state,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "deleted_at" TIMESTAMP(3),
  "depth" BIGINT
);
CREATE INDEX discussion_entry_dim_workflow_state ON discussion_entry_dim(workflow_state);

CREATE TABLE IF NOT EXISTS discussion_entry_fact (
  "discussion_entry_id" BIGINT PRIMARY KEY,
  "parent_discussion_entry_id" BIGINT,
  "user_id" BIGINT,
  "topic_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "course_account_id" BIGINT,
  "topic_user_id" BIGINT,
  "topic_assignment_id" BIGINT,
  "topic_editor_id" BIGINT,
  "enrollment_rollup_id" BIGINT,
  "message_length" BIGINT
);
CREATE INDEX discussion_entry_fact_parent_discussion_entry_id ON discussion_entry_fact(parent_discussion_entry_id);
CREATE INDEX discussion_entry_fact_user_id ON discussion_entry_fact(user_id);
CREATE INDEX discussion_entry_fact_topic_id ON discussion_entry_fact(topic_id);
CREATE INDEX discussion_entry_fact_course_id ON discussion_entry_fact(course_id);
CREATE INDEX discussion_entry_fact_enrollment_term_id ON discussion_entry_fact(enrollment_term_id);
CREATE INDEX discussion_entry_fact_course_account_id ON discussion_entry_fact(course_account_id);
CREATE INDEX discussion_entry_fact_topic_user_id ON discussion_entry_fact(topic_user_id);
CREATE INDEX discussion_entry_fact_topic_assignment_id ON discussion_entry_fact(topic_assignment_id);
CREATE INDEX discussion_entry_fact_topic_editor_id ON discussion_entry_fact(topic_editor_id);
CREATE INDEX discussion_entry_fact_enrollment_rollup_id ON discussion_entry_fact(enrollment_rollup_id);

CREATE TABLE IF NOT EXISTS enrollment_term_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "root_account_id" BIGINT,
  "name" VARCHAR(256),
  "date_start" TIMESTAMP(3),
  "date_end" TIMESTAMP(3),
  "sis_source_id" VARCHAR(256)
);
CREATE INDEX enrollment_term_dim_root_account_id ON enrollment_term_dim(root_account_id);
CREATE INDEX enrollment_term_dim_sis_source_id ON enrollment_term_dim(sis_source_id);

CREATE TYPE course_section_dim_default_section AS ENUM('false','true');
CREATE TYPE course_section_dim_accepting_enrollments AS ENUM('false','true');
CREATE TYPE course_section_dim_can_manually_enroll AS ENUM('false','true');
CREATE TYPE course_section_dim_workflow_state AS ENUM('deleted', 'inactive','active');
CREATE TYPE course_section_dim_restrict_enrollments_to_section_dates AS ENUM('false','true');
CREATE TABLE IF NOT EXISTS course_section_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "name" VARCHAR(256),
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "default_section" course_section_dim_default_section,
  "accepting_enrollments" course_section_dim_accepting_enrollments,
  "can_manually_enroll" course_section_dim_can_manually_enroll,
  "start_at" TIMESTAMP(3),
  "end_at" TIMESTAMP(3),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "workflow_state" course_section_dim_workflow_state,
  "restrict_enrollments_to_section_dates" course_section_dim_restrict_enrollments_to_section_dates,
  "nonxlist_course_id" BIGINT,
  "sis_source_id" VARCHAR(256)
);
CREATE INDEX course_section_dim_course_id ON course_section_dim(course_id);
CREATE INDEX course_section_dim_enrollment_term_id ON course_section_dim(enrollment_term_id);
CREATE INDEX course_section_dim_workflow_state ON course_section_dim(workflow_state);
CREATE INDEX course_section_dim_nonxlist_course_id ON course_section_dim(nonxlist_course_id);
CREATE INDEX course_section_dim_sis_source_id ON course_section_dim(sis_source_id);

CREATE TYPE role_dim_workflow_state AS ENUM('active', 'deleted', 'built_in');
CREATE TABLE IF NOT EXISTS role_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "root_account_id" BIGINT,
  "account_id" BIGINT,
  "name" VARCHAR(256),
  "base_role_type" VARCHAR(256),
  "workflow_state" role_dim_workflow_state,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "deleted_at" TIMESTAMP(3)
);
CREATE INDEX role_dim_root_account_id ON role_dim(root_account_id);
CREATE INDEX role_dim_account_id ON role_dim(account_id);
CREATE INDEX role_dim_workflow_state ON role_dim(workflow_state);

CREATE TYPE enrollment_dim_workflow_state AS ENUM('active','inactive','completed', 'rejected', 'deleted', 'invited', 'creation_pending');
CREATE TYPE enrollment_dim_self_enrolled AS ENUM('False','True');
CREATE TABLE IF NOT EXISTS enrollment_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "root_account_id" BIGINT,
  "course_section_id" BIGINT,
  "role_id" BIGINT,
  "type" VARCHAR(256),
  "workflow_state" enrollment_dim_workflow_state,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "start_at" TIMESTAMP(3),
  "end_at" TIMESTAMP(3),
  "completed_at" TIMESTAMP(3),
  "self_enrolled" enrollment_dim_self_enrolled,
  "sis_source_id" VARCHAR(256),
  "course_id" BIGINT,
  "user_id" BIGINT,
  "last_activity_at" TIMESTAMP(3)
);
CREATE INDEX enrollment_dim_root_account_id ON enrollment_dim(root_account_id);
CREATE INDEX enrollment_dim_course_section_id ON enrollment_dim(course_section_id);
CREATE INDEX enrollment_dim_role_id ON enrollment_dim(role_id);
CREATE INDEX enrollment_dim_workflow_state ON enrollment_dim(workflow_state);
CREATE INDEX enrollment_dim_sis_source_id ON enrollment_dim(sis_source_id);
CREATE INDEX enrollment_dim_course_id ON enrollment_dim(course_id);
CREATE INDEX enrollment_dim_user_id ON enrollment_dim(user_id);

CREATE TABLE IF NOT EXISTS enrollment_fact (
  "enrollment_id" BIGINT PRIMARY KEY,
  "user_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "course_account_id" BIGINT,
  "course_section_id" BIGINT,
  "computed_final_score" DOUBLE PRECISION,
  "computed_current_score" DOUBLE PRECISION
);
CREATE INDEX enrollment_fact_user_id ON enrollment_fact(user_id);
CREATE INDEX enrollment_fact_course_id ON enrollment_fact(course_id);
CREATE INDEX enrollment_fact_enrollment_term_id ON enrollment_fact(enrollment_term_id);
CREATE INDEX enrollment_fact_course_account_id ON enrollment_fact(course_account_id);
CREATE INDEX enrollment_fact_course_section_id ON enrollment_fact(course_section_id);

CREATE TABLE IF NOT EXISTS enrollment_rollup_dim (
  "id" BIGINT PRIMARY KEY,
  "user_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_count" BIGINT,
  "role_count" BIGINT,
  "base_role_count" BIGINT,
  "account_admin_role_count" BIGINT,
  "teacher_enrollment_role_count" BIGINT,
  "designer_enrollment_role_count" BIGINT,
  "ta_enrollment_role_count" BIGINT,
  "student_enrollment_role_count" BIGINT,
  "observer_enrollment_role_count" BIGINT,
  "account_membership_role_count" BIGINT,
  "no_permissions_role_count" BIGINT,
  "account_admin_enrollment_id" BIGINT,
  "teacher_enrollment_enrollment_id" BIGINT,
  "designer_enrollment_enrollment_id" BIGINT,
  "ta_enrollment_enrollment_id" BIGINT,
  "student_enrollment_enrollment_id" BIGINT,
  "observer_enrollment_enrollment_id" BIGINT,
  "account_membership_enrollment_id" BIGINT,
  "no_permissions_enrollment_id" BIGINT,
  "most_privileged_role" VARCHAR(256),
  "least_privileged_role" VARCHAR(256)
);
CREATE INDEX enrollment_rollup_dim_user_id ON enrollment_rollup_dim(user_id);
CREATE INDEX enrollment_rollup_dim_course_id ON enrollment_rollup_dim(course_id);
CREATE INDEX enrollment_rollup_dim_account_admin_enrollment_id ON enrollment_rollup_dim(account_admin_enrollment_id);
CREATE INDEX enrollment_rollup_dim_teacher_enrollment_enrollment_id ON enrollment_rollup_dim(teacher_enrollment_enrollment_id);
CREATE INDEX enrollment_rollup_dim_designer_enrollment_enrollment_id ON enrollment_rollup_dim(designer_enrollment_enrollment_id);
CREATE INDEX enrollment_rollup_dim_ta_enrollment_enrollment_id ON enrollment_rollup_dim(ta_enrollment_enrollment_id);
CREATE INDEX enrollment_rollup_dim_student_enrollment_enrollment_id ON enrollment_rollup_dim(student_enrollment_enrollment_id);
CREATE INDEX enrollment_rollup_dim_observer_enrollment_enrollment_id ON enrollment_rollup_dim(observer_enrollment_enrollment_id);
CREATE INDEX enrollment_rollup_dim_account_membership_enrollment_id ON enrollment_rollup_dim(account_membership_enrollment_id);
CREATE INDEX enrollment_rollup_dim_no_permissions_enrollment_id ON enrollment_rollup_dim(no_permissions_enrollment_id);

CREATE TABLE IF NOT EXISTS assignment_group_score_fact (
  "score_id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "assignment_group_id" BIGINT,
  "enrollment_id" BIGINT,
  "current_score" DOUBLE PRECISION,
  "final_score" DOUBLE PRECISION,
  "muted_current_score" DOUBLE PRECISION,
  "muted_final_score" DOUBLE PRECISION
);
CREATE INDEX assignment_group_score_fact_canvas_id ON assignment_group_score_fact(canvas_id);
CREATE INDEX assignment_group_score_fact_account_id ON assignment_group_score_fact(account_id);
CREATE INDEX assignment_group_score_fact_course_id ON assignment_group_score_fact(course_id);
CREATE INDEX assignment_group_score_fact_assignment_group_id ON assignment_group_score_fact(assignment_group_id);
CREATE INDEX assignment_group_score_fact_enrollment_id ON assignment_group_score_fact(enrollment_id);

CREATE TABLE IF NOT EXISTS course_score_fact (
  "score_id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_id" BIGINT,
  "current_score" DOUBLE PRECISION,
  "final_score" DOUBLE PRECISION,
  "muted_current_score" DOUBLE PRECISION,
  "muted_final_score" DOUBLE PRECISION
);
CREATE INDEX course_score_fact_canvas_id ON course_score_fact(canvas_id);
CREATE INDEX course_score_fact_account_id ON course_score_fact(account_id);
CREATE INDEX course_score_fact_course_id ON course_score_fact(course_id);
CREATE INDEX course_score_fact_enrollment_id ON course_score_fact(enrollment_id);

CREATE TABLE IF NOT EXISTS grading_period_score_fact (
  "score_id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_id" BIGINT,
  "grading_period_id" BIGINT,
  "grading_period_group_id" BIGINT,
  "grading_period_group_account_id" BIGINT,
  "current_score" DOUBLE PRECISION,
  "final_score" DOUBLE PRECISION,
  "muted_current_score" DOUBLE PRECISION,
  "muted_final_score" DOUBLE PRECISION
);
CREATE INDEX grading_period_score_fact_canvas_id ON grading_period_score_fact(canvas_id);
CREATE INDEX grading_period_score_fact_account_id ON grading_period_score_fact(account_id);
CREATE INDEX grading_period_score_fact_course_id ON grading_period_score_fact(course_id);
CREATE INDEX grading_period_score_fact_enrollment_id ON grading_period_score_fact(enrollment_id);
CREATE INDEX grading_period_score_fact_grading_period_id ON grading_period_score_fact(grading_period_id);
CREATE INDEX grading_period_score_fact_grading_period_group_id ON grading_period_score_fact(grading_period_group_id);
CREATE INDEX grading_period_score_fact_grading_period_group_account_id ON grading_period_score_fact(grading_period_group_account_id);

CREATE TYPE assignment_group_score_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TABLE IF NOT EXISTS assignment_group_score_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "assignment_group_id" BIGINT,
  "enrollment_id" BIGINT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "workflow_state" assignment_group_score_dim_workflow_state
);
CREATE INDEX assignment_group_score_dim_assignment_group_id ON assignment_group_score_dim(assignment_group_id);
CREATE INDEX assignment_group_score_dim_enrollment_id ON assignment_group_score_dim(enrollment_id);
CREATE INDEX assignment_group_score_dim_workflow_state ON assignment_group_score_dim(workflow_state);

CREATE TYPE course_score_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TABLE IF NOT EXISTS course_score_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "enrollment_id" BIGINT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "workflow_state" course_score_dim_workflow_state
);
CREATE INDEX course_score_dim_enrollment_id ON course_score_dim(enrollment_id);
CREATE INDEX course_score_dim_workflow_state ON course_score_dim(workflow_state);

CREATE TYPE grading_period_score_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TABLE IF NOT EXISTS grading_period_score_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "enrollment_id" BIGINT,
  "grading_period_id" BIGINT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "workflow_state" grading_period_score_dim_workflow_state
);
CREATE INDEX grading_period_score_dim_enrollment_id ON grading_period_score_dim(enrollment_id);
CREATE INDEX grading_period_score_dim_grading_period_id ON grading_period_score_dim(grading_period_id);
CREATE INDEX grading_period_score_dim_workflow_state ON grading_period_score_dim(workflow_state);

CREATE TABLE IF NOT EXISTS grading_period_fact (
  "grading_period_id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT,
  "grading_period_group_id" BIGINT,
  "grading_period_group_account_id" BIGINT,
  "grading_period_group_course_id" BIGINT,
  "weight" DOUBLE PRECISION
);
CREATE INDEX grading_period_fact_canvas_id ON grading_period_fact(canvas_id);
CREATE INDEX grading_period_fact_grading_period_group_id ON grading_period_fact(grading_period_group_id);
CREATE INDEX grading_period_fact_grading_period_group_account_id ON grading_period_fact(grading_period_group_account_id);
CREATE INDEX grading_period_fact_grading_period_group_course_id ON grading_period_fact(grading_period_group_course_id);

CREATE TYPE grading_period_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TABLE IF NOT EXISTS grading_period_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "grading_period_group_id" BIGINT,
  "close_date" TIMESTAMP(3),
  "created_at" TIMESTAMP(3),
  "end_date" TIMESTAMP(3),
  "start_date" TIMESTAMP(3),
  "title" VARCHAR(256),
  "updated_at" TIMESTAMP(3),
  "workflow_state" grading_period_dim_workflow_state
);
CREATE INDEX grading_period_dim_grading_period_group_id ON grading_period_dim(grading_period_group_id);
CREATE INDEX grading_period_dim_workflow_state ON grading_period_dim(workflow_state);

CREATE TYPE grading_period_group_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TABLE IF NOT EXISTS grading_period_group_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "course_id" BIGINT,
  "account_id" BIGINT,
  "created_at" TIMESTAMP(3),
  "title" VARCHAR(256),
  "updated_at" TIMESTAMP(3),
  "workflow_state" grading_period_group_dim_workflow_state
);
CREATE INDEX grading_period_group_dim_course_id ON grading_period_group_dim(course_id);
CREATE INDEX grading_period_group_dim_account_id ON grading_period_group_dim(account_id);
CREATE INDEX grading_period_group_dim_workflow_state ON grading_period_group_dim(workflow_state);

CREATE TYPE file_dim_owner_entity_type AS ENUM('account', 'assignment', 'conversation_message', 'course', 'group', 'quiz', 'quiz_submission', 'submission', 'user');
CREATE TYPE file_dim_file_state AS ENUM('available', 'broken', 'deleted', 'errored', 'hidden');
CREATE TYPE file_dim_could_be_locked AS ENUM('allow_locking', 'disallow_locking');
CREATE TYPE file_dim_locked AS ENUM('is_locked', 'is_not_locked');
CREATE TABLE IF NOT EXISTS file_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "display_name" TEXT,
  "account_id" BIGINT,
  "assignment_id" BIGINT,
  "conversation_message_id" BIGINT,
  "course_id" BIGINT,
  "folder_id" BIGINT,
  "group_id" BIGINT,
  "quiz_id" BIGINT,
  "quiz_submission_id" BIGINT,
  "replacement_file_id" BIGINT,
  "root_file_id" BIGINT,
  "submission_id" BIGINT,
  "uploader_id" BIGINT,
  "user_id" BIGINT,
  "owner_entity_type" file_dim_owner_entity_type,
  "content_type" VARCHAR(256),
  "md5" VARCHAR(256),
  "file_state" file_dim_file_state,
  "could_be_locked" file_dim_could_be_locked,
  "locked" file_dim_locked,
  "lock_at" TIMESTAMP(3),
  "unlock_at" TIMESTAMP(3),
  "viewed_at" TIMESTAMP(3),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "deleted_at" TIMESTAMP(3)
);
CREATE INDEX file_dim_account_id ON file_dim(account_id);
CREATE INDEX file_dim_assignment_id ON file_dim(assignment_id);
CREATE INDEX file_dim_conversation_message_id ON file_dim(conversation_message_id);
CREATE INDEX file_dim_course_id ON file_dim(course_id);
CREATE INDEX file_dim_folder_id ON file_dim(folder_id);
CREATE INDEX file_dim_group_id ON file_dim(group_id);
CREATE INDEX file_dim_quiz_id ON file_dim(quiz_id);
CREATE INDEX file_dim_quiz_submission_id ON file_dim(quiz_submission_id);
CREATE INDEX file_dim_replacement_file_id ON file_dim(replacement_file_id);
CREATE INDEX file_dim_root_file_id ON file_dim(root_file_id);
CREATE INDEX file_dim_submission_id ON file_dim(submission_id);
CREATE INDEX file_dim_uploader_id ON file_dim(uploader_id);
CREATE INDEX file_dim_user_id ON file_dim(user_id);

CREATE TABLE IF NOT EXISTS file_fact (
  "file_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "assignment_id" BIGINT,
  "assignment_group_id" BIGINT,
  "conversation_id" BIGINT,
  "conversation_message_author_id" BIGINT,
  "conversation_message_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_rollup_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "folder_id" BIGINT,
  "grader_id" BIGINT,
  "group_id" BIGINT,
  "group_category_id" BIGINT,
  "quiz_id" BIGINT,
  "quiz_submission_id" BIGINT,
  "replacement_file_id" BIGINT,
  "root_file_id" BIGINT,
  "sis_source_id" VARCHAR(256),
  "submission_id" BIGINT,
  "uploader_id" BIGINT,
  "user_id" BIGINT,
  "wiki_id" BIGINT,
  "size" BIGINT
);
CREATE INDEX file_fact_account_id ON file_fact(account_id);
CREATE INDEX file_fact_assignment_id ON file_fact(assignment_id);
CREATE INDEX file_fact_assignment_group_id ON file_fact(assignment_group_id);
CREATE INDEX file_fact_conversation_id ON file_fact(conversation_id);
CREATE INDEX file_fact_conversation_message_author_id ON file_fact(conversation_message_author_id);
CREATE INDEX file_fact_conversation_message_id ON file_fact(conversation_message_id);
CREATE INDEX file_fact_course_id ON file_fact(course_id);
CREATE INDEX file_fact_enrollment_rollup_id ON file_fact(enrollment_rollup_id);
CREATE INDEX file_fact_enrollment_term_id ON file_fact(enrollment_term_id);
CREATE INDEX file_fact_folder_id ON file_fact(folder_id);
CREATE INDEX file_fact_grader_id ON file_fact(grader_id);
CREATE INDEX file_fact_group_id ON file_fact(group_id);
CREATE INDEX file_fact_group_category_id ON file_fact(group_category_id);
CREATE INDEX file_fact_quiz_id ON file_fact(quiz_id);
CREATE INDEX file_fact_quiz_submission_id ON file_fact(quiz_submission_id);
CREATE INDEX file_fact_replacement_file_id ON file_fact(replacement_file_id);
CREATE INDEX file_fact_root_file_id ON file_fact(root_file_id);
CREATE INDEX file_fact_sis_source_id ON file_fact(sis_source_id);
CREATE INDEX file_fact_submission_id ON file_fact(submission_id);
CREATE INDEX file_fact_uploader_id ON file_fact(uploader_id);
CREATE INDEX file_fact_user_id ON file_fact(user_id);
CREATE INDEX file_fact_wiki_id ON file_fact(wiki_id);

CREATE TYPE group_dim_is_public AS ENUM('False','True');
CREATE TYPE group_dim_workflow_state AS ENUM('active', 'available', 'deleted');
CREATE TABLE IF NOT EXISTS group_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "name" VARCHAR(256),
  "description" TEXT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "deleted_at" TIMESTAMP(3),
  "is_public" group_dim_is_public,
  "workflow_state" group_dim_workflow_state,
  "context_type" VARCHAR(256),
  "category" TEXT,
  "join_level" VARCHAR(256),
  "default_view" VARCHAR(256),
  "sis_source_id" BIGINT,
  "group_category_id" BIGINT,
  "account_id" BIGINT,
  "wiki_id" BIGINT
);
CREATE INDEX group_dim_workflow_state ON group_dim(workflow_state);
CREATE INDEX group_dim_sis_source_id ON group_dim(sis_source_id);
CREATE INDEX group_dim_group_category_id ON group_dim(group_category_id);
CREATE INDEX group_dim_account_id ON group_dim(account_id);
CREATE INDEX group_dim_wiki_id ON group_dim(wiki_id);

CREATE TABLE IF NOT EXISTS group_fact (
  "group_id" BIGINT PRIMARY KEY,
  "parent_course_id" BIGINT,
  "parent_account_id" BIGINT,
  "parent_course_account_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "max_membership" BIGINT,
  "storage_quota" BIGINT,
  "group_category_id" BIGINT,
  "account_id" BIGINT,
  "wiki_id" BIGINT
);
CREATE INDEX group_fact_parent_course_id ON group_fact(parent_course_id);
CREATE INDEX group_fact_parent_account_id ON group_fact(parent_account_id);
CREATE INDEX group_fact_parent_course_account_id ON group_fact(parent_course_account_id);
CREATE INDEX group_fact_enrollment_term_id ON group_fact(enrollment_term_id);
CREATE INDEX group_fact_group_category_id ON group_fact(group_category_id);
CREATE INDEX group_fact_account_id ON group_fact(account_id);
CREATE INDEX group_fact_wiki_id ON group_fact(wiki_id);

CREATE TABLE IF NOT EXISTS group_membership_fact (
  "group_id" BIGINT,
  "parent_course_id" BIGINT,
  "parent_account_id" BIGINT,
  "parent_course_account_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "user_id" BIGINT,
  "group_membership_id" VARCHAR(256) PRIMARY KEY
);
CREATE INDEX group_membership_fact_group_id ON group_membership_fact(group_id);
CREATE INDEX group_membership_fact_parent_course_id ON group_membership_fact(parent_course_id);
CREATE INDEX group_membership_fact_parent_account_id ON group_membership_fact(parent_account_id);
CREATE INDEX group_membership_fact_parent_course_account_id ON group_membership_fact(parent_course_account_id);
CREATE INDEX group_membership_fact_enrollment_term_id ON group_membership_fact(enrollment_term_id);
CREATE INDEX group_membership_fact_user_id ON group_membership_fact(user_id);

CREATE TYPE group_membership_dim_moderator AS ENUM('is_moderator', 'not_moderator');
CREATE TYPE group_membership_dim_workflow_state AS ENUM('accepted', 'invited', 'requested', 'deleted');
CREATE TABLE IF NOT EXISTS group_membership_dim (
  "id" VARCHAR(256) PRIMARY KEY,
  "canvas_id" VARCHAR(256) UNIQUE,
  "group_id" BIGINT,
  "moderator" group_membership_dim_moderator,
  "workflow_state" group_membership_dim_workflow_state,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX group_membership_dim_group_id ON group_membership_dim(group_id);
CREATE INDEX group_membership_dim_workflow_state ON group_membership_dim(workflow_state);

CREATE TYPE module_dim_require_sequential_progress AS ENUM('required', 'not_required', 'unspecified', 'not_specified');
CREATE TYPE module_dim_workflow_state AS ENUM('locked', 'completed', 'unlocked', 'started','active','deleted','unpublished');
CREATE TABLE IF NOT EXISTS module_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "course_id" BIGINT,
  "require_sequential_progress" module_dim_require_sequential_progress,
  "workflow_state" module_dim_workflow_state,
  "position" BIGINT,
  "name" TEXT,
  "created_at" TIMESTAMP(3),
  "deleted_at" TIMESTAMP(3),
  "unlock_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX module_dim_course_id ON module_dim(course_id);
CREATE INDEX module_dim_workflow_state ON module_dim(workflow_state);

CREATE TABLE IF NOT EXISTS module_fact (
  "module_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "wiki_id" BIGINT
);
CREATE INDEX module_fact_account_id ON module_fact(account_id);
CREATE INDEX module_fact_course_id ON module_fact(course_id);
CREATE INDEX module_fact_enrollment_term_id ON module_fact(enrollment_term_id);
CREATE INDEX module_fact_wiki_id ON module_fact(wiki_id);

CREATE TYPE module_item_dim_content_type AS ENUM('Assignment', 'Attachment', 'DiscussionTopic', 'ContextExternalTool', 'ContextModuleSubHeader', 'ExternalUrl', 'LearningOutcome', 'Quiz', 'Rubric', 'WikiPage');
CREATE TYPE module_item_dim_workflow_state AS ENUM('active', 'unpublished', 'deleted');
CREATE TABLE IF NOT EXISTS module_item_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "assignment_id" BIGINT,
  "course_id" BIGINT,
  "discussion_topic_id" BIGINT,
  "file_id" BIGINT,
  "module_id" BIGINT,
  "quiz_id" BIGINT,
  "wiki_page_id" BIGINT,
  "content_type" module_item_dim_content_type,
  "workflow_state" module_item_dim_workflow_state,
  "position" BIGINT,
  "title" TEXT,
  "url" TEXT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX module_item_dim_assignment_id ON module_item_dim(assignment_id);
CREATE INDEX module_item_dim_course_id ON module_item_dim(course_id);
CREATE INDEX module_item_dim_discussion_topic_id ON module_item_dim(discussion_topic_id);
CREATE INDEX module_item_dim_file_id ON module_item_dim(file_id);
CREATE INDEX module_item_dim_module_id ON module_item_dim(module_id);
CREATE INDEX module_item_dim_quiz_id ON module_item_dim(quiz_id);
CREATE INDEX module_item_dim_wiki_page_id ON module_item_dim(wiki_page_id);
CREATE INDEX module_item_dim_workflow_state ON module_item_dim(workflow_state);

CREATE TABLE IF NOT EXISTS module_item_fact (
  "module_item_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "assignment_id" BIGINT,
  "assignment_group_id" BIGINT,
  "course_id" BIGINT,
  "discussion_topic_id" BIGINT,
  "discussion_topic_editor_id" BIGINT,
  "enrollment_rollup_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "file_id" BIGINT,
  "module_id" BIGINT,
  "quiz_id" BIGINT,
  "user_id" BIGINT,
  "wiki_id" BIGINT,
  "wiki_page_id" BIGINT
);
CREATE INDEX module_item_fact_account_id ON module_item_fact(account_id);
CREATE INDEX module_item_fact_assignment_id ON module_item_fact(assignment_id);
CREATE INDEX module_item_fact_assignment_group_id ON module_item_fact(assignment_group_id);
CREATE INDEX module_item_fact_course_id ON module_item_fact(course_id);
CREATE INDEX module_item_fact_discussion_topic_id ON module_item_fact(discussion_topic_id);
CREATE INDEX module_item_fact_discussion_topic_editor_id ON module_item_fact(discussion_topic_editor_id);
CREATE INDEX module_item_fact_enrollment_rollup_id ON module_item_fact(enrollment_rollup_id);
CREATE INDEX module_item_fact_enrollment_term_id ON module_item_fact(enrollment_term_id);
CREATE INDEX module_item_fact_file_id ON module_item_fact(file_id);
CREATE INDEX module_item_fact_module_id ON module_item_fact(module_id);
CREATE INDEX module_item_fact_quiz_id ON module_item_fact(quiz_id);
CREATE INDEX module_item_fact_user_id ON module_item_fact(user_id);
CREATE INDEX module_item_fact_wiki_id ON module_item_fact(wiki_id);
CREATE INDEX module_item_fact_wiki_page_id ON module_item_fact(wiki_page_id);

CREATE TYPE module_progression_dim_collapsed AS ENUM('collapsed', 'not_collapsed', 'unspecified','false', 'true');
CREATE TYPE module_progression_dim_is_current AS ENUM('current', 'not_current', 'unspecified', 'true', 'false');
CREATE TYPE module_progression_dim_workflow_state AS ENUM('locked', 'completed', 'unlocked', 'started');
CREATE TABLE IF NOT EXISTS module_progression_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "module_id" BIGINT,
  "user_id" BIGINT,
  "collapsed" module_progression_dim_collapsed,
  "is_current" module_progression_dim_is_current,
  "workflow_state" module_progression_dim_workflow_state,
  "current_position" BIGINT,
  "lock_version" BIGINT,
  "created_at" TIMESTAMP(3),
  "completed_at" TIMESTAMP(3),
  "evaluated_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX module_progression_dim_module_id ON module_progression_dim(module_id);
CREATE INDEX module_progression_dim_user_id ON module_progression_dim(user_id);
CREATE INDEX module_progression_dim_workflow_state ON module_progression_dim(workflow_state);

CREATE TABLE IF NOT EXISTS module_progression_fact (
  "module_progression_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "module_id" BIGINT,
  "user_id" BIGINT,
  "wiki_id" BIGINT
);
CREATE INDEX module_progression_fact_account_id ON module_progression_fact(account_id);
CREATE INDEX module_progression_fact_course_id ON module_progression_fact(course_id);
CREATE INDEX module_progression_fact_enrollment_term_id ON module_progression_fact(enrollment_term_id);
CREATE INDEX module_progression_fact_module_id ON module_progression_fact(module_id);
CREATE INDEX module_progression_fact_user_id ON module_progression_fact(user_id);
CREATE INDEX module_progression_fact_wiki_id ON module_progression_fact(wiki_id);

CREATE TYPE module_completion_requirement_dim_requirement_type AS ENUM('must_view', 'must_mark_done', 'min_score', 'must_submit','must_contribute');
CREATE TABLE IF NOT EXISTS module_completion_requirement_dim (
  "id" BIGINT PRIMARY KEY,
  "module_id" BIGINT,
  "module_item_id" BIGINT,
  "requirement_type" module_completion_requirement_dim_requirement_type
);
CREATE INDEX module_completion_requirement_dim_module_id ON module_completion_requirement_dim(module_id);
CREATE INDEX module_completion_requirement_dim_module_item_id ON module_completion_requirement_dim(module_item_id);

CREATE TABLE IF NOT EXISTS module_completion_requirement_fact (
  "module_completion_requirement_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "assignment_id" BIGINT,
  "assignment_group_id" BIGINT,
  "course_id" BIGINT,
  "discussion_topic_id" BIGINT,
  "discussion_topic_editor_id" BIGINT,
  "enrollment_rollup_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "file_id" BIGINT,
  "module_id" BIGINT,
  "module_item_id" BIGINT,
  "quiz_id" BIGINT,
  "user_id" BIGINT,
  "wiki_id" BIGINT,
  "wiki_page_id" BIGINT,
  "min_score" DOUBLE PRECISION
);
CREATE INDEX module_completion_requirement_fact_account_id ON module_completion_requirement_fact(account_id);
CREATE INDEX module_completion_requirement_fact_assignment_id ON module_completion_requirement_fact(assignment_id);
CREATE INDEX module_completion_requirement_fact_assignment_group_id ON module_completion_requirement_fact(assignment_group_id);
CREATE INDEX module_completion_requirement_fact_course_id ON module_completion_requirement_fact(course_id);
CREATE INDEX module_completion_requirement_fact_discussion_topic_id ON module_completion_requirement_fact(discussion_topic_id);
CREATE INDEX module_completion_requirement_fact_discussion_topic_editor_id ON module_completion_requirement_fact(discussion_topic_editor_id);
CREATE INDEX module_completion_requirement_fact_enrollment_rollup_id ON module_completion_requirement_fact(enrollment_rollup_id);
CREATE INDEX module_completion_requirement_fact_enrollment_term_id ON module_completion_requirement_fact(enrollment_term_id);
CREATE INDEX module_completion_requirement_fact_file_id ON module_completion_requirement_fact(file_id);
CREATE INDEX module_completion_requirement_fact_module_id ON module_completion_requirement_fact(module_id);
CREATE INDEX module_completion_requirement_fact_module_item_id ON module_completion_requirement_fact(module_item_id);
CREATE INDEX module_completion_requirement_fact_quiz_id ON module_completion_requirement_fact(quiz_id);
CREATE INDEX module_completion_requirement_fact_user_id ON module_completion_requirement_fact(user_id);
CREATE INDEX module_completion_requirement_fact_wiki_id ON module_completion_requirement_fact(wiki_id);
CREATE INDEX module_completion_requirement_fact_wiki_page_id ON module_completion_requirement_fact(wiki_page_id);

CREATE TABLE IF NOT EXISTS module_prerequisite_dim (
  "id" BIGINT PRIMARY KEY,
  "module_id" BIGINT,
  "prerequisite_module_id" BIGINT
);
CREATE INDEX module_prerequisite_dim_module_id ON module_prerequisite_dim(module_id);
CREATE INDEX module_prerequisite_dim_prerequisite_module_id ON module_prerequisite_dim(prerequisite_module_id);

CREATE TABLE IF NOT EXISTS module_prerequisite_fact (
  "module_prerequisite_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "module_id" BIGINT,
  "prerequisite_module_id" BIGINT,
  "prerequisite_wiki_id" BIGINT,
  "wiki_id" BIGINT
);
CREATE INDEX module_prerequisite_fact_account_id ON module_prerequisite_fact(account_id);
CREATE INDEX module_prerequisite_fact_course_id ON module_prerequisite_fact(course_id);
CREATE INDEX module_prerequisite_fact_enrollment_term_id ON module_prerequisite_fact(enrollment_term_id);
CREATE INDEX module_prerequisite_fact_module_id ON module_prerequisite_fact(module_id);
CREATE INDEX module_prerequisite_fact_prerequisite_module_id ON module_prerequisite_fact(prerequisite_module_id);
CREATE INDEX module_prerequisite_fact_prerequisite_wiki_id ON module_prerequisite_fact(prerequisite_wiki_id);
CREATE INDEX module_prerequisite_fact_wiki_id ON module_prerequisite_fact(wiki_id);

CREATE TYPE mpcr_dim_requirement_type AS ENUM('must_view', 'must_mark_done', 'min_score', 'must_submit', 'must_contribute');
CREATE TYPE mpcr_dim_completion_status AS ENUM('complete', 'incomplete');
CREATE TABLE IF NOT EXISTS module_progression_completion_requirement_dim (
  "id" BIGINT PRIMARY KEY,
  "module_progression_id" BIGINT,
  "module_item_id" BIGINT,
  "requirement_type" mpcr_dim_requirement_type,
  "completion_status" mpcr_dim_completion_status
);
CREATE INDEX mpcr_dim_module_progression_id ON module_progression_completion_requirement_dim(module_progression_id);
CREATE INDEX mpcr_dim_module_item_id ON module_progression_completion_requirement_dim(module_item_id);

CREATE TABLE IF NOT EXISTS module_progression_completion_requirement_fact (
  "module_progression_completion_requirement_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "assignment_id" BIGINT,
  "assignment_group_id" BIGINT,
  "course_id" BIGINT,
  "discussion_topic_id" BIGINT,
  "discussion_topic_editor_id" BIGINT,
  "enrollment_rollup_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "file_id" BIGINT,
  "module_id" BIGINT,
  "module_item_id" BIGINT,
  "module_progression_id" BIGINT,
  "quiz_id" BIGINT,
  "user_id" BIGINT,
  "wiki_id" BIGINT,
  "wiki_page_id" BIGINT,
  "min_score" DOUBLE PRECISION,
  "score" DOUBLE PRECISION
);
CREATE INDEX mpcr_fact_account_id ON module_progression_completion_requirement_fact(account_id);
CREATE INDEX mpcr_fact_assignment_id ON module_progression_completion_requirement_fact(assignment_id);
CREATE INDEX mpcr_fact_assignment_group_id ON module_progression_completion_requirement_fact(assignment_group_id);
CREATE INDEX mpcr_fact_course_id ON module_progression_completion_requirement_fact(course_id);
CREATE INDEX mpcr_fact_discussion_topic_id ON module_progression_completion_requirement_fact(discussion_topic_id);
CREATE INDEX mpcr_fact_discussion_topic_editor_id ON module_progression_completion_requirement_fact(discussion_topic_editor_id);
CREATE INDEX mpcr_fact_enrollment_rollup_id ON module_progression_completion_requirement_fact(enrollment_rollup_id);
CREATE INDEX mpcr_fact_enrollment_term_id ON module_progression_completion_requirement_fact(enrollment_term_id);
CREATE INDEX mpcr_fact_file_id ON module_progression_completion_requirement_fact(file_id);
CREATE INDEX mpcr_fact_module_id ON module_progression_completion_requirement_fact(module_id);
CREATE INDEX mpcr_fact_module_item_id ON module_progression_completion_requirement_fact(module_item_id);
CREATE INDEX mpcr_fact_module_progression_id ON module_progression_completion_requirement_fact(module_progression_id);
CREATE INDEX mpcr_fact_quiz_id ON module_progression_completion_requirement_fact(quiz_id);
CREATE INDEX mpcr_fact_user_id ON module_progression_completion_requirement_fact(user_id);
CREATE INDEX mpcr_fact_wiki_id ON module_progression_completion_requirement_fact(wiki_id);
CREATE INDEX mpcr_fact_wiki_page_id ON module_progression_completion_requirement_fact(wiki_page_id);

CREATE TABLE IF NOT EXISTS course_ui_canvas_navigation_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "name" VARCHAR(256),
  "default" VARCHAR(256),
  "original_position" VARCHAR(256)
);

CREATE TABLE IF NOT EXISTS course_ui_navigation_item_dim (
  "id" BIGINT PRIMARY KEY,
  "root_account_id" BIGINT,
  "visible" VARCHAR(256),
  "position" BIGINT
);
CREATE INDEX course_ui_navigation_item_dim_root_account_id ON course_ui_navigation_item_dim(root_account_id);

CREATE TABLE IF NOT EXISTS course_ui_navigation_item_fact (
  "root_account_id" BIGINT,
  "course_ui_navigation_item_id" BIGINT PRIMARY KEY,
  "course_ui_canvas_navigation_id" BIGINT,
  "external_tool_activation_id" BIGINT,
  "course_id" BIGINT,
  "course_account_id" BIGINT,
  "enrollment_term_id" BIGINT
);
CREATE INDEX course_ui_navigation_item_fact_root_account_id ON course_ui_navigation_item_fact(root_account_id);
CREATE INDEX course_ui_navigation_item_fact_course_ui_canvas_navigation_id ON course_ui_navigation_item_fact(course_ui_canvas_navigation_id);
CREATE INDEX course_ui_navigation_item_fact_external_tool_activation_id ON course_ui_navigation_item_fact(external_tool_activation_id);
CREATE INDEX course_ui_navigation_item_fact_course_id ON course_ui_navigation_item_fact(course_id);
CREATE INDEX course_ui_navigation_item_fact_course_account_id ON course_ui_navigation_item_fact(course_account_id);
CREATE INDEX course_ui_navigation_item_fact_enrollment_term_id ON course_ui_navigation_item_fact(enrollment_term_id);

CREATE TYPE learning_outcome_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TABLE IF NOT EXISTS learning_outcome_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "short_description" VARCHAR(256),
  "description" TEXT,
  "workflow_state" learning_outcome_dim_workflow_state,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "vendor_guid" VARCHAR(256),
  "display_name" VARCHAR(256),
  "calculation_method" TEXT,
  "calculation_int" BIGINT,
  "outcome_import_id" BIGINT
);
CREATE INDEX learning_outcome_dim_account_id ON learning_outcome_dim(account_id);
CREATE INDEX learning_outcome_dim_course_id ON learning_outcome_dim(course_id);
CREATE INDEX learning_outcome_dim_workflow_state ON learning_outcome_dim(workflow_state);
CREATE INDEX learning_outcome_dim_outcome_import_id ON learning_outcome_dim(outcome_import_id);

CREATE TABLE IF NOT EXISTS learning_outcome_fact (
  "learning_outcome_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "mastery_points" DOUBLE PRECISION,
  "points_possible" DOUBLE PRECISION,
  "outcome_import_id" BIGINT
);
CREATE INDEX learning_outcome_fact_account_id ON learning_outcome_fact(account_id);
CREATE INDEX learning_outcome_fact_course_id ON learning_outcome_fact(course_id);
CREATE INDEX learning_outcome_fact_enrollment_term_id ON learning_outcome_fact(enrollment_term_id);
CREATE INDEX learning_outcome_fact_outcome_import_id ON learning_outcome_fact(outcome_import_id);

CREATE TABLE IF NOT EXISTS learning_outcome_rubric_criterion_dim (
  "id" BIGINT PRIMARY KEY,
  "learning_outcome_id" BIGINT,
  "description" TEXT
);
CREATE INDEX learning_outcome_rubric_criterion_dim_learning_outcome_id ON learning_outcome_rubric_criterion_dim(learning_outcome_id);

CREATE TABLE IF NOT EXISTS learning_outcome_rubric_criterion_fact (
  "learning_outcome_rubric_criterion_id" BIGINT PRIMARY KEY,
  "learning_outcome_id" BIGINT,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "points" DOUBLE PRECISION
);
CREATE INDEX learning_outcome_rubric_criterion_fact_learning_outcome_id ON learning_outcome_rubric_criterion_fact(learning_outcome_id);
CREATE INDEX learning_outcome_rubric_criterion_fact_account_id ON learning_outcome_rubric_criterion_fact(account_id);
CREATE INDEX learning_outcome_rubric_criterion_fact_course_id ON learning_outcome_rubric_criterion_fact(course_id);
CREATE INDEX learning_outcome_rubric_criterion_fact_enrollment_term_id ON learning_outcome_rubric_criterion_fact(enrollment_term_id);

CREATE TYPE learning_outcome_group_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TABLE IF NOT EXISTS learning_outcome_group_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "title" VARCHAR(256),
  "parent_group_id" BIGINT,
  "root_group_id" BIGINT,
  "workflow_state" learning_outcome_group_dim_workflow_state,
  "description" TEXT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "vendor_guid" VARCHAR(256),
  "outcome_import_id" BIGINT
);
CREATE INDEX learning_outcome_group_dim_account_id ON learning_outcome_group_dim(account_id);
CREATE INDEX learning_outcome_group_dim_course_id ON learning_outcome_group_dim(course_id);
CREATE INDEX learning_outcome_group_dim_parent_group_id ON learning_outcome_group_dim(parent_group_id);
CREATE INDEX learning_outcome_group_dim_root_group_id ON learning_outcome_group_dim(root_group_id);
CREATE INDEX learning_outcome_group_dim_workflow_state ON learning_outcome_group_dim(workflow_state);
CREATE INDEX learning_outcome_group_dim_outcome_import_id ON learning_outcome_group_dim(outcome_import_id);

CREATE TABLE IF NOT EXISTS learning_outcome_group_fact (
  "learning_outcome_group_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "parent_group_id" BIGINT,
  "root_group_id" BIGINT,
  "outcome_import_id" BIGINT
);
CREATE INDEX learning_outcome_group_fact_account_id ON  learning_outcome_group_fact(account_id);
CREATE INDEX learning_outcome_group_fact_course_id ON  learning_outcome_group_fact(course_id);
CREATE INDEX learning_outcome_group_fact_enrollment_term_id ON  learning_outcome_group_fact(enrollment_term_id);
CREATE INDEX learning_outcome_group_fact_parent_group_id ON  learning_outcome_group_fact(parent_group_id);
CREATE INDEX learning_outcome_group_fact_root_group_id ON  learning_outcome_group_fact(root_group_id);
CREATE INDEX learning_outcome_group_fact_outcome_import_id ON  learning_outcome_group_fact(outcome_import_id);

CREATE TABLE IF NOT EXISTS learning_outcome_group_association_fact (
  "learning_outcome_id" BIGINT,
  "learning_outcome_group_id" BIGINT,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT
);
CREATE INDEX loga_fact_learning_outcome_id ON learning_outcome_group_association_fact(learning_outcome_id);
CREATE INDEX loga_fact_learning_outcome_group_id ON learning_outcome_group_association_fact(learning_outcome_group_id);
CREATE INDEX loga_fact_account_id ON learning_outcome_group_association_fact(account_id);
CREATE INDEX loga_fact_course_id ON learning_outcome_group_association_fact(course_id);
CREATE INDEX loga_fact_enrollment_term_id ON learning_outcome_group_association_fact(enrollment_term_id);

CREATE TYPE learning_outcome_result_dim_hide_points AS ENUM('False','True');
CREATE TYPE learning_outcome_result_dim_hidden AS ENUM('False','True');
CREATE TABLE IF NOT EXISTS learning_outcome_result_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "assignment_id" BIGINT,
  "quiz_id" BIGINT,
  "learning_outcome_id" BIGINT,
  "user_id" BIGINT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "assessed_at" TIMESTAMP(3),
  "submitted_at" TIMESTAMP(3),
  "hide_points" learning_outcome_result_dim_hide_points,
  "hidden" learning_outcome_result_dim_hidden
);
CREATE INDEX learning_outcome_result_dim_account_id ON learning_outcome_result_dim(account_id);
CREATE INDEX learning_outcome_result_dim_course_id ON learning_outcome_result_dim(course_id);
CREATE INDEX learning_outcome_result_dim_assignment_id ON learning_outcome_result_dim(assignment_id);
CREATE INDEX learning_outcome_result_dim_quiz_id ON learning_outcome_result_dim(quiz_id);
CREATE INDEX learning_outcome_result_dim_learning_outcome_id ON learning_outcome_result_dim(learning_outcome_id);
CREATE INDEX learning_outcome_result_dim_user_id ON learning_outcome_result_dim(user_id);

CREATE TYPE learning_outcome_result_fact_mastery AS ENUM('False','True','true','false');
CREATE TYPE learning_outcome_result_fact_original_mastery AS ENUM('False','True','true','false');
CREATE TABLE IF NOT EXISTS learning_outcome_result_fact (
  "learning_outcome_result_id" BIGINT PRIMARY KEY,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "assignment_id" BIGINT,
  "quiz_id" BIGINT,
  "learning_outcome_id" BIGINT,
  "user_id" BIGINT,
  "mastery" learning_outcome_result_fact_mastery,
  "score" DOUBLE PRECISION,
  "attempts" BIGINT,
  "possible" DOUBLE PRECISION,
  "original_score" DOUBLE PRECISION,
  "original_possible" DOUBLE PRECISION,
  "original_mastery" learning_outcome_result_fact_original_mastery,
  "percent" DOUBLE PRECISION
);
CREATE INDEX learning_outcome_result_fact_account_id ON learning_outcome_result_fact(account_id);
CREATE INDEX learning_outcome_result_fact_course_id ON learning_outcome_result_fact(course_id);
CREATE INDEX learning_outcome_result_fact_enrollment_term_id ON learning_outcome_result_fact(enrollment_term_id);
CREATE INDEX learning_outcome_result_fact_assignment_id ON learning_outcome_result_fact(assignment_id);
CREATE INDEX learning_outcome_result_fact_quiz_id ON learning_outcome_result_fact(quiz_id);
CREATE INDEX learning_outcome_result_fact_learning_outcome_id ON learning_outcome_result_fact(learning_outcome_id);
CREATE INDEX learning_outcome_result_fact_user_id ON learning_outcome_result_fact(user_id);

CREATE TABLE IF NOT EXISTS learning_outcome_question_result_dim (
  "id" BIGINT PRIMARY KEY,
  "learning_outcome_result_id" BIGINT,
  "learning_outcome_id" BIGINT,
  "assessment_question_id" BIGINT,
  "title" TEXT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "assessed_at" TIMESTAMP(3),
  "submitted_at" TIMESTAMP(3)
);
CREATE INDEX learning_outcome_question_result_dim_learning_outcome_result_id ON learning_outcome_question_result_dim(learning_outcome_result_id);
CREATE INDEX learning_outcome_question_result_dim_learning_outcome_id ON learning_outcome_question_result_dim(learning_outcome_id);
CREATE INDEX learning_outcome_question_result_dim_assessment_question_id ON learning_outcome_question_result_dim(assessment_question_id);

CREATE TYPE learning_outcome_question_result_fact_mastery AS ENUM('False','True');
CREATE TYPE learning_outcome_question_result_fact_original_mastery AS ENUM('False','True');
CREATE TABLE IF NOT EXISTS learning_outcome_question_result_fact (
  "learning_outcome_question_result_id" BIGINT PRIMARY KEY,
  "learning_outcome_result_id" BIGINT,
  "learning_outcome_id" BIGINT,
  "assessment_question_id" BIGINT,
  "user_id" BIGINT,
  "account_id" BIGINT,
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "assignment_id" BIGINT,
  "quiz_id" BIGINT,
  "mastery" learning_outcome_question_result_fact_mastery,
  "score" DOUBLE PRECISION,
  "attempts" BIGINT,
  "possible" DOUBLE PRECISION,
  "original_score" DOUBLE PRECISION,
  "original_possible" DOUBLE PRECISION,
  "original_mastery" learning_outcome_question_result_fact_original_mastery,
  "percent" DOUBLE PRECISION
);
CREATE INDEX loqr_fact_learning_outcome_result_id ON learning_outcome_question_result_fact(learning_outcome_result_id);
CREATE INDEX loqr_fact_learning_outcome_id ON learning_outcome_question_result_fact(learning_outcome_id);
CREATE INDEX loqr_fact_assessment_question_id ON learning_outcome_question_result_fact(assessment_question_id);
CREATE INDEX loqr_fact_user_id ON learning_outcome_question_result_fact(user_id);
CREATE INDEX loqr_fact_account_id ON learning_outcome_question_result_fact(account_id);
CREATE INDEX loqr_fact_course_id ON learning_outcome_question_result_fact(course_id);
CREATE INDEX loqr_fact_enrollment_term_id ON learning_outcome_question_result_fact(enrollment_term_id);
CREATE INDEX loqr_fact_assignment_id ON learning_outcome_question_result_fact(assignment_id);
CREATE INDEX loqr_fact_quiz_id ON learning_outcome_question_result_fact(quiz_id);

CREATE TYPE quiz_dim_quiz_type AS ENUM('practice_quiz', 'assignment', 'graded_survey', 'survey', 'NULL');
CREATE TYPE quiz_dim_workflow_state AS ENUM('unpublished', 'published', 'deleted') ;
CREATE TYPE quiz_dim_scoring_policy AS ENUM('keep_highest', 'keep_latest', 'keep_average') ;
CREATE TYPE quiz_dim_anonymous_submissions AS ENUM('allow_anonymous_submissions', 'disallow_anonymous_submissions');
CREATE TYPE quiz_dim_display_questions AS ENUM('multiple_at_a_time', 'one_at_a_time', 'one_question_at_a_time');
CREATE TYPE quiz_dim_answer_display_order AS ENUM('in_order', 'shuffled', 'shuffle_answers');
CREATE TYPE quiz_dim_go_back_to_previous_question AS ENUM('display_questions', 'one_at_a_time', 'allow_going_back', 'disallow_going_back', 'cant_go_back');
CREATE TYPE quiz_dim_could_be_locked AS ENUM('allow_locking', 'disallow_locking');
CREATE TYPE quiz_dim_browser_lockdown AS ENUM('required', 'not_required');
CREATE TYPE quiz_dim_browser_lockdown_for_displaying_results AS ENUM('hide_results', 'never', 'until_after_last_attempt', 'required', 'not_required');
CREATE TYPE quiz_dim_browser_lockdown_monitor AS ENUM('required', 'not_required');
CREATE TYPE quiz_dim_show_results AS ENUM('always', 'never', 'always_after_last_attempt', 'only_once_after_last_attempt');
CREATE TYPE quiz_dim_show_correct_answers AS ENUM('always', 'never', 'always_after_last_attempt', 'only_once_after_last_attempt');
CREATE TABLE IF NOT EXISTS quiz_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "root_account_id" BIGINT,
  "name" VARCHAR(256),
  "points_possible" DOUBLE PRECISION,
  "description" TEXT,
  "quiz_type" quiz_dim_quiz_type,
  "course_id" BIGINT,
  "assignment_id" BIGINT,
  "workflow_state" quiz_dim_workflow_state,
  "scoring_policy" quiz_dim_scoring_policy,
  "anonymous_submissions" quiz_dim_anonymous_submissions,
  "display_questions" quiz_dim_display_questions,
  "answer_display_order" quiz_dim_answer_display_order,
  "go_back_to_previous_question" quiz_dim_go_back_to_previous_question,
  "could_be_locked" quiz_dim_could_be_locked,
  "browser_lockdown" quiz_dim_browser_lockdown,
  "browser_lockdown_for_displaying_results" quiz_dim_browser_lockdown_for_displaying_results,
  "browser_lockdown_monitor" quiz_dim_browser_lockdown_monitor,
  "ip_filter" VARCHAR(256),
  "show_results" quiz_dim_show_results,
  "show_correct_answers" quiz_dim_show_correct_answers,
  "show_correct_answers_at" TIMESTAMP(3),
  "hide_correct_answers_at" TIMESTAMP(3),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "published_at" TIMESTAMP(3),
  "unlock_at" TIMESTAMP(3),
  "lock_at" TIMESTAMP(3),
  "due_at" TIMESTAMP(3),
  "deleted_at" TIMESTAMP(3)
);
CREATE INDEX quiz_dim_root_account_id ON quiz_dim(root_account_id);
CREATE INDEX quiz_dim_course_id ON quiz_dim(course_id);
CREATE INDEX quiz_dim_assignment_id ON quiz_dim(assignment_id);
CREATE INDEX quiz_dim_workflow_state ON quiz_dim(workflow_state);

CREATE TABLE IF NOT EXISTS quiz_fact (
  "quiz_id" BIGINT PRIMARY KEY,
  "points_possible" DOUBLE PRECISION,
  "time_limit" BIGINT,
  "allowed_attempts" BIGINT,
  "unpublished_question_count" BIGINT,
  "question_count" BIGINT,
  "course_id" BIGINT,
  "assignment_id" BIGINT,
  "course_account_id" BIGINT,
  "enrollment_term_id" BIGINT
);
CREATE INDEX quiz_fact_course_id ON quiz_fact(course_id);
CREATE INDEX quiz_fact_assignment_id ON quiz_fact(assignment_id);
CREATE INDEX quiz_fact_course_account_id ON quiz_fact(course_account_id);
CREATE INDEX quiz_fact_enrollment_term_id ON quiz_fact(enrollment_term_id);

CREATE TYPE quiz_submission_historical_dim_submission_state AS ENUM('current_submission', 'previous_submission');
CREATE TYPE quiz_submission_historical_dim_workflow_state AS ENUM('untaken', 'complete', 'pending_review', 'preview', 'settings_only');
CREATE TYPE quiz_submission_historical_dim_submission_scoring_policy AS ENUM('keep_highest', 'keep_latest', 'keep_average', 'manually_overridden');
CREATE TYPE quiz_submission_historical_dim_submission_source AS ENUM('student', 'test_preview');
CREATE TABLE IF NOT EXISTS quiz_submission_historical_dim (
  "id" BIGINT,
  "canvas_id" BIGINT,
  "quiz_id" BIGINT,
  "submission_id" BIGINT,
  "user_id" BIGINT,
  "version_number" BIGINT,
  "submission_state" quiz_submission_historical_dim_submission_state,
  "workflow_state" quiz_submission_historical_dim_workflow_state,
  "quiz_state_during_submission" VARCHAR(256),
  "submission_scoring_policy" quiz_submission_historical_dim_submission_scoring_policy,
  "submission_source" quiz_submission_historical_dim_submission_source,
  "has_seen_results" VARCHAR(256),
  "temporary_user_code" VARCHAR(256),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "started_at" TIMESTAMP(3),
  "finished_at" TIMESTAMP(3),
  "due_at" TIMESTAMP(3)
);
CREATE INDEX quiz_submission_historical_dim_id ON quiz_submission_historical_dim(id);
CREATE INDEX quiz_submission_historical_dim_canvas_id ON quiz_submission_historical_dim(canvas_id);
CREATE INDEX quiz_submission_historical_dim_quiz_id ON quiz_submission_historical_dim(quiz_id);
CREATE INDEX quiz_submission_historical_dim_submission_id ON quiz_submission_historical_dim(submission_id);
CREATE INDEX quiz_submission_historical_dim_user_id ON quiz_submission_historical_dim(user_id);
CREATE INDEX quiz_submission_historical_dim_workflow_state ON quiz_submission_historical_dim(workflow_state);

CREATE TABLE IF NOT EXISTS quiz_submission_historical_fact (
  "score" DOUBLE PRECISION,
  "kept_score" DOUBLE PRECISION,
  "date" TIMESTAMP(3),
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "course_account_id" BIGINT,
  "quiz_id" BIGINT,
  "assignment_id" BIGINT,
  "user_id" BIGINT,
  "submission_id" BIGINT,
  "enrollment_rollup_id" BIGINT,
  "quiz_submission_historical_id" BIGINT PRIMARY KEY,
  "quiz_points_possible" DOUBLE PRECISION,
  "score_before_regrade" DOUBLE PRECISION,
  "fudge_points" DOUBLE PRECISION,
  "total_attempts" BIGINT,
  "extra_attempts" BIGINT,
  "extra_time" BIGINT,
  "time_taken" BIGINT
);
CREATE INDEX quiz_submission_historical_fact_course_id ON quiz_submission_historical_fact(course_id);
CREATE INDEX quiz_submission_historical_fact_enrollment_term_id ON quiz_submission_historical_fact(enrollment_term_id);
CREATE INDEX quiz_submission_historical_fact_course_account_id ON quiz_submission_historical_fact(course_account_id);
CREATE INDEX quiz_submission_historical_fact_quiz_id ON quiz_submission_historical_fact(quiz_id);
CREATE INDEX quiz_submission_historical_fact_assignment_id ON quiz_submission_historical_fact(assignment_id);
CREATE INDEX quiz_submission_historical_fact_user_id ON quiz_submission_historical_fact(user_id);
CREATE INDEX quiz_submission_historical_fact_submission_id ON quiz_submission_historical_fact(submission_id);
CREATE INDEX quiz_submission_historical_fact_enrollment_rollup_id ON quiz_submission_historical_fact(enrollment_rollup_id);

CREATE TYPE quiz_submission_dim_workflow_state AS ENUM('untaken', 'complete', 'pending_review', 'preview', 'settings_only', 'essay_question');
CREATE TYPE quiz_submission_dim_submission_scoring_policy AS ENUM('keep_highest', 'keep_latest', 'keep_average','manually_overridden');
CREATE TYPE quiz_submission_dim_submission_source AS ENUM('student', 'test_preview');
CREATE TABLE IF NOT EXISTS quiz_submission_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "quiz_id" BIGINT,
  "submission_id" BIGINT,
  "user_id" BIGINT,
  "workflow_state" quiz_submission_dim_workflow_state,
  "quiz_state_during_submission" VARCHAR(256),
  "submission_scoring_policy" quiz_submission_dim_submission_scoring_policy,
  "submission_source" quiz_submission_dim_submission_source,
  "has_seen_results" VARCHAR(256),
  "temporary_user_code" VARCHAR(256),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "started_at" TIMESTAMP(3),
  "finished_at" TIMESTAMP(3),
  "due_at" TIMESTAMP(3)
);
CREATE INDEX quiz_submission_dim_quiz_id ON quiz_submission_dim(quiz_id);
CREATE INDEX quiz_submission_dim_submission_id ON quiz_submission_dim(submission_id);
CREATE INDEX quiz_submission_dim_user_id ON quiz_submission_dim(user_id);
CREATE INDEX quiz_submission_dim_workflow_state ON quiz_submission_dim(workflow_state);

CREATE TABLE IF NOT EXISTS quiz_submission_fact (
  "score" DOUBLE PRECISION,
  "kept_score" DOUBLE PRECISION,
  "date" TIMESTAMP(3),
  "course_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "course_account_id" BIGINT,
  "quiz_id" BIGINT,
  "assignment_id" BIGINT,
  "user_id" BIGINT,
  "submission_id" BIGINT,
  "enrollment_rollup_id" BIGINT,
  "quiz_submission_id" BIGINT PRIMARY KEY,
  "quiz_points_possible" DOUBLE PRECISION,
  "score_before_regrade" DOUBLE PRECISION,
  "fudge_points" DOUBLE PRECISION,
  "total_attempts" BIGINT,
  "extra_attempts" BIGINT,
  "extra_time" BIGINT,
  "time_taken" BIGINT
);
CREATE INDEX quiz_submission_fact_course_id ON quiz_submission_fact(course_id);
CREATE INDEX quiz_submission_fact_enrollment_term_id ON quiz_submission_fact(enrollment_term_id);
CREATE INDEX quiz_submission_fact_course_account_id ON quiz_submission_fact(course_account_id);
CREATE INDEX quiz_submission_fact_quiz_id ON quiz_submission_fact(quiz_id);
CREATE INDEX quiz_submission_fact_assignment_id ON quiz_submission_fact(assignment_id);
CREATE INDEX quiz_submission_fact_user_id ON quiz_submission_fact(user_id);
CREATE INDEX quiz_submission_fact_submission_id ON quiz_submission_fact(submission_id);
CREATE INDEX quiz_submission_fact_enrollment_rollup_id ON quiz_submission_fact(enrollment_rollup_id);

CREATE TABLE IF NOT EXISTS quiz_question_group_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "quiz_id" BIGINT,
  "name" VARCHAR(256),
  "position" BIGINT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3)
);
CREATE INDEX quiz_question_group_dim_quiz_id ON quiz_question_group_dim(quiz_id);

CREATE TABLE IF NOT EXISTS quiz_question_group_fact (
  "quiz_question_group_id" BIGINT PRIMARY KEY,
  "pick_count" BIGINT,
  "question_points" DOUBLE PRECISION,
  "quiz_id" BIGINT,
  "course_id" BIGINT,
  "assignment_id" BIGINT,
  "course_account_id" BIGINT,
  "enrollment_term_id" BIGINT
);
CREATE INDEX quiz_question_group_fact_quiz_id ON quiz_question_group_fact(quiz_id);
CREATE INDEX quiz_question_group_fact_course_id ON quiz_question_group_fact(course_id);
CREATE INDEX quiz_question_group_fact_assignment_id ON quiz_question_group_fact(assignment_id);
CREATE INDEX quiz_question_group_fact_course_account_id ON quiz_question_group_fact(course_account_id);
CREATE INDEX quiz_question_group_fact_enrollment_term_id ON quiz_question_group_fact(enrollment_term_id);

CREATE TYPE quiz_question_dim_workflow_state AS ENUM('unpublished', 'published', 'deleted');
CREATE TYPE quiz_question_dim_question_type AS ENUM('calculated_question', 'essay_question', 'file_upload_question', 'fill_in_multiple_blanks_question', 'matching_question', 'multiple_answers_question', 'multiple_choice_question', 'multiple_dropdowns_question', 'numerical_question', 'short_answer_question', 'text_only_question', 'true_false_question');
CREATE TYPE quiz_question_dim_regrade_option AS ENUM('available', 'unavailable', 'multiple_answers_question', 'multiple_choice_question', 'True_False_question', 'NULL');
CREATE TABLE IF NOT EXISTS quiz_question_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "quiz_id" BIGINT,
  "quiz_question_group_id" BIGINT,
  "position" BIGINT,
  "workflow_state" quiz_question_dim_workflow_state,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "assessment_question_id" BIGINT,
  "assessment_question_version" BIGINT,
  "name" VARCHAR(256),
  "question_type" quiz_question_dim_question_type,
  "question_text" TEXT,
  "regrade_option" quiz_question_dim_regrade_option,
  "correct_comments" TEXT,
  "incorrect_comments" TEXT,
  "neutral_comments" TEXT
);
CREATE INDEX quiz_question_dim_quiz_id ON quiz_question_dim(quiz_id);
CREATE INDEX quiz_question_dim_quiz_question_group_id ON quiz_question_dim(quiz_question_group_id);
CREATE INDEX quiz_question_dim_workflow_state ON quiz_question_dim(workflow_state);
CREATE INDEX quiz_question_dim_assessment_question_id ON quiz_question_dim(assessment_question_id);

CREATE TABLE IF NOT EXISTS quiz_question_fact (
  "quiz_question_id" BIGINT PRIMARY KEY,
  "quiz_id" BIGINT,
  "quiz_question_group_id" BIGINT,
  "assessment_question_id" BIGINT,
  "course_id" BIGINT,
  "assignment_id" BIGINT,
  "course_account_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "points_possible" DOUBLE PRECISION
);
CREATE INDEX quiz_question_fact_quiz_id ON quiz_question_fact(quiz_id);
CREATE INDEX quiz_question_fact_quiz_question_group_id ON quiz_question_fact(quiz_question_group_id);
CREATE INDEX quiz_question_fact_assessment_question_id ON quiz_question_fact(assessment_question_id);
CREATE INDEX quiz_question_fact_course_id ON quiz_question_fact(course_id);
CREATE INDEX quiz_question_fact_assignment_id ON quiz_question_fact(assignment_id);
CREATE INDEX quiz_question_fact_course_account_id ON quiz_question_fact(course_account_id);
CREATE INDEX quiz_question_fact_enrollment_term_id ON quiz_question_fact(enrollment_term_id);

CREATE TYPE quiz_question_answer_dim_numerical_answer_type AS ENUM('numerical_question', 'NULL', 'exact_answer', 'range_answer','precision_answer', '\\N');
CREATE TABLE IF NOT EXISTS quiz_question_answer_dim (
  "id" BIGINT,
  "canvas_id" BIGINT,
  "quiz_question_id" BIGINT,
  "text" TEXT,
  "html" TEXT,
  "comments" TEXT,
  "text_after_answers" TEXT,
  "answer_match_left" TEXT,
  "answer_match_right" TEXT,
  "matching_answer_incorrect_matches" TEXT,
  "numerical_answer_type" quiz_question_answer_dim_numerical_answer_type,
  "blank_id" VARCHAR(256),
  "exact" DOUBLE PRECISION,
  "margin" DOUBLE PRECISION,
  "starting_range" DOUBLE PRECISION,
  "ending_range" DOUBLE PRECISION
);
CREATE INDEX quiz_question_answer_dim_id ON quiz_question_answer_dim(id);
CREATE INDEX quiz_question_answer_dim_canvas_id ON quiz_question_answer_dim(canvas_id);
CREATE INDEX quiz_question_answer_dim_quiz_question_id ON quiz_question_answer_dim(quiz_question_id);
CREATE INDEX quiz_question_answer_dim_blank_id ON quiz_question_answer_dim(blank_id);

CREATE TABLE IF NOT EXISTS quiz_question_answer_fact (
  "quiz_question_answer_id" BIGINT PRIMARY KEY,
  "quiz_question_id" BIGINT,
  "quiz_question_group_id" BIGINT,
  "quiz_id" BIGINT,
  "assessment_question_id" BIGINT,
  "course_id" BIGINT,
  "assignment_id" BIGINT,
  "course_account_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "weight" DOUBLE PRECISION,
  "exact" DOUBLE PRECISION,
  "margin" DOUBLE PRECISION,
  "starting_range" DOUBLE PRECISION,
  "ending_range" DOUBLE PRECISION
);
CREATE INDEX quiz_question_answer_fact_quiz_question_id ON quiz_question_answer_fact(quiz_question_id);
CREATE INDEX quiz_question_answer_fact_quiz_question_group_id ON quiz_question_answer_fact(quiz_question_group_id);
CREATE INDEX quiz_question_answer_fact_quiz_id ON quiz_question_answer_fact(quiz_id);
CREATE INDEX quiz_question_answer_fact_assessment_question_id ON quiz_question_answer_fact(assessment_question_id);
CREATE INDEX quiz_question_answer_fact_course_id ON quiz_question_answer_fact(course_id);
CREATE INDEX quiz_question_answer_fact_assignment_id ON quiz_question_answer_fact(assignment_id);
CREATE INDEX quiz_question_answer_fact_course_account_id ON quiz_question_answer_fact(course_account_id);
CREATE INDEX quiz_question_answer_fact_enrollment_term_id ON quiz_question_answer_fact(enrollment_term_id);

CREATE TABLE IF NOT EXISTS requests (
  "local_id" BIGSERIAL PRIMARY KEY,
  "id" VARCHAR(36),
  "timestamp" TIMESTAMP(3),
  "user_id" BIGINT,
  "course_id" BIGINT,
  "quiz_id" BIGINT,
  "discussion_id" BIGINT,
  "conversation_id" BIGINT,
  "assignment_id" BIGINT,
  "url" TEXT,
  "user_agent" TEXT
);
CREATE INDEX requests_id ON requests(id);
CREATE INDEX requests_course_id_request_index ON requests(course_id);

CREATE TYPE external_tool_activation_dim_workflow_state AS ENUM('active', 'deleted');
CREATE TYPE external_tool_activation_dim_selectable_all AS ENUM('False','True');
CREATE TABLE IF NOT EXISTS external_tool_activation_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "course_id" BIGINT,
  "account_id" BIGINT,
  "activation_target_type" VARCHAR(256),
  "url" VARCHAR(4096),
  "name" VARCHAR(256),
  "description" VARCHAR(256),
  "workflow_state" external_tool_activation_dim_workflow_state,
  "privacy_level" VARCHAR(256),
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "tool_id" VARCHAR(256),
  "selectable_all" external_tool_activation_dim_selectable_all
);
CREATE INDEX external_tool_activation_dim_course_id ON external_tool_activation_dim(course_id);
CREATE INDEX external_tool_activation_dim_account_id ON external_tool_activation_dim(account_id);
CREATE INDEX external_tool_activation_dim_workflow_state ON external_tool_activation_dim(workflow_state);
CREATE INDEX external_tool_activation_dim_tool_id ON external_tool_activation_dim(tool_id);

CREATE TABLE IF NOT EXISTS external_tool_activation_fact (
  "external_tool_activation_id" BIGINT PRIMARY KEY,
  "course_id" BIGINT,
  "account_id" BIGINT,
  "root_account_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "course_account_id" BIGINT
);
CREATE INDEX external_tool_activation_fact_course_id ON external_tool_activation_fact(course_id);
CREATE INDEX external_tool_activation_fact_account_id ON external_tool_activation_fact(account_id);
CREATE INDEX external_tool_activation_fact_root_account_id ON external_tool_activation_fact(root_account_id);
CREATE INDEX external_tool_activation_fact_enrollment_term_id ON external_tool_activation_fact(enrollment_term_id);
CREATE INDEX external_tool_activation_fact_course_account_id ON external_tool_activation_fact(course_account_id);

CREATE TYPE wiki_dim_has_no_front_page AS ENUM('False','True');
CREATE TABLE IF NOT EXISTS wiki_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "parent_type" VARCHAR(256),
  "title" TEXT,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "front_page_url" TEXT,
  "has_no_front_page" wiki_dim_has_no_front_page
);

CREATE TABLE IF NOT EXISTS wiki_fact (
  "wiki_id" BIGINT PRIMARY KEY,
  "parent_course_id" BIGINT,
  "parent_group_id" BIGINT,
  "parent_course_account_id" BIGINT,
  "parent_group_account_id" BIGINT,
  "account_id" BIGINT,
  "root_account_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "group_category_id" BIGINT
);
CREATE INDEX wiki_fact_parent_course_id ON wiki_fact(parent_course_id);
CREATE INDEX wiki_fact_parent_group_id ON wiki_fact(parent_group_id);
CREATE INDEX wiki_fact_parent_course_account_id ON wiki_fact(parent_course_account_id);
CREATE INDEX wiki_fact_parent_group_account_id ON wiki_fact(parent_group_account_id);
CREATE INDEX wiki_fact_account_id ON wiki_fact(account_id);
CREATE INDEX wiki_fact_root_account_id ON wiki_fact(root_account_id);
CREATE INDEX wiki_fact_enrollment_term_id ON wiki_fact(enrollment_term_id);
CREATE INDEX wiki_fact_group_category_id ON wiki_fact(group_category_id);

CREATE TYPE wiki_page_dim_workflow_state AS ENUM('active', 'unpublished', 'deleted');
CREATE TYPE wiki_page_dim_protected_editing AS ENUM('False','True');
CREATE TYPE wiki_page_dim_could_be_locked AS ENUM('False','True');
CREATE TABLE IF NOT EXISTS wiki_page_dim (
  "id" BIGINT PRIMARY KEY,
  "canvas_id" BIGINT UNIQUE,
  "title" VARCHAR(1000),
  "body" TEXT,
  "workflow_state" wiki_page_dim_workflow_state,
  "created_at" TIMESTAMP(3),
  "updated_at" TIMESTAMP(3),
  "url" TEXT,
  "protected_editing" wiki_page_dim_protected_editing,
  "editing_roles" VARCHAR(256),
  "revised_at" TIMESTAMP(3),
  "could_be_locked" wiki_page_dim_could_be_locked
);
CREATE INDEX wiki_page_dim_workflow_state ON wiki_page_dim(workflow_state);

CREATE TABLE IF NOT EXISTS wiki_page_fact (
  "wiki_page_id" BIGINT PRIMARY KEY,
  "wiki_id" BIGINT,
  "parent_course_id" BIGINT,
  "parent_group_id" BIGINT,
  "parent_course_account_id" BIGINT,
  "parent_group_account_id" BIGINT,
  "user_id" BIGINT,
  "account_id" BIGINT,
  "root_account_id" BIGINT,
  "enrollment_term_id" BIGINT,
  "group_category_id" BIGINT,
  "wiki_page_comments_count" BIGINT,
  "view_count" BIGINT
);
CREATE INDEX wiki_page_fact_wiki_id ON wiki_page_fact(wiki_id);
CREATE INDEX wiki_page_fact_parent_course_id ON wiki_page_fact(parent_course_id);
CREATE INDEX wiki_page_fact_parent_group_id ON wiki_page_fact(parent_group_id);
CREATE INDEX wiki_page_fact_parent_course_account_id ON wiki_page_fact(parent_course_account_id);
CREATE INDEX wiki_page_fact_parent_group_account_id ON wiki_page_fact(parent_group_account_id);
CREATE INDEX wiki_page_fact_user_id ON wiki_page_fact(user_id);
CREATE INDEX wiki_page_fact_account_id ON wiki_page_fact(account_id);
CREATE INDEX wiki_page_fact_root_account_id ON wiki_page_fact(root_account_id);
CREATE INDEX wiki_page_fact_enrollment_term_id ON wiki_page_fact(enrollment_term_id);
CREATE INDEX wiki_page_fact_group_category_id ON wiki_page_fact(group_category_id);
