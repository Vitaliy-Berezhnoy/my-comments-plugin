<?php

function get_name_active_db():string {
    if (isset($_COOKIE['current_db'])) {
        return sanitize_text_field($_COOKIE['current_db']);
    }
    return 'mysql';     // БД по умолчанию mysql
}
