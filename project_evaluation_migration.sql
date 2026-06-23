-- Non-destructive migration for Project Evaluation System
-- Use this if your database already exists and the full dump import fails.

START TRANSACTION;

-- 1) Add projects.assigned_to_all if missing
SET @has_assigned_to_all := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'projects'
    AND column_name = 'assigned_to_all'
);

SET @sql := IF(@has_assigned_to_all = 0,
  'ALTER TABLE projects ADD COLUMN assigned_to_all TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Create project_assignments if missing
SET @has_project_assignments := (
  SELECT COUNT(*)
  FROM information_schema.tables
  WHERE table_schema = DATABASE()
    AND table_name = 'project_assignments'
);

SET @sql := IF(@has_project_assignments = 0,
  'CREATE TABLE project_assignments (
     id INT(11) NOT NULL AUTO_INCREMENT,
     project_id INT(11) NOT NULL,
     lecturer_id INT(11) NOT NULL,
     assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
     PRIMARY KEY (id)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Add indexes (safe if they already exist)
-- project_id index
SET @has_pa_project_idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'project_assignments'
    AND index_name = 'project_assignments_project_id'
);
SET @sql := IF(@has_pa_project_idx = 0,
  'CREATE INDEX project_assignments_project_id ON project_assignments (project_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- lecturer_id index
SET @has_pa_lecturer_idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'project_assignments'
    AND index_name = 'project_assignments_lecturer_id'
);
SET @sql := IF(@has_pa_lecturer_idx = 0,
  'CREATE INDEX project_assignments_lecturer_id ON project_assignments (lecturer_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

COMMIT;
