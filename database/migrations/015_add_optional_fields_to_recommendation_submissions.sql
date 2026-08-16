ALTER TABLE recommendation_submissions
    ADD COLUMN photo_path VARCHAR(500) NULL AFTER quote,
    ADD COLUMN social_url VARCHAR(500) NULL AFTER photo_path;
