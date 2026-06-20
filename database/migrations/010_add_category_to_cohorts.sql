ALTER TABLE cohorts
    ADD COLUMN category_id BIGINT UNSIGNED NULL AFTER meta_label,
    ADD KEY cohorts_category_id_index (category_id),
    ADD CONSTRAINT cohorts_category_id_fk FOREIGN KEY (category_id)
        REFERENCES cohort_categories (id) ON DELETE SET NULL;
