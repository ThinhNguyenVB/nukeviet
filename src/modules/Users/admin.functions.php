<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 12/31/2009 2:29
 */

if (!defined('NV_ADMIN') or !defined('NV_MAINFILE') or !defined('NV_IS_MODADMIN')) {
    die('Stop!!!');
}

define('NV_IS_FILE_ADMIN', true);

// Tài liệu
$array_url_instruction['main'] = 'https://wiki.nukeviet.vn/nukeviet4:admin:users';
$array_url_instruction['user_add'] = 'https://wiki.nukeviet.vn/nukeviet4:admin:users#them_tai_khoản_mới';
$array_url_instruction['user_waiting'] = 'https://wiki.nukeviet.vn/nukeviet4:admin:users#thanh_vien_dợi_kich_hoạt';
$array_url_instruction['groups'] = 'https://wiki.nukeviet.vn/nukeviet4:admin:users#nhom_thanh_vien';
$array_url_instruction['question'] = 'https://wiki.nukeviet.vn/nukeviet4:admin:users#cau_hỏi_bảo_mật';
$array_url_instruction['siteterms'] = 'https://wiki.nukeviet.vn/nukeviet4:admin:users#nội_quy_site';
$array_url_instruction['fields'] = 'https://wiki.nukeviet.vn/nukeviet4:admin:users#tuy_biến_dữ_liệu';
$array_url_instruction['config'] = 'https://wiki.nukeviet.vn/nukeviet4:admin:users#cấu_hinh_module_thanh_vien';
$array_url_instruction['editcensor'] = 'https://wiki.nukeviet.vn/nukeviet4:admin:users#kiểm_duyệt_thong_tin_chỉnh_sửa_của_thanh_vien';

define('NV_MOD_TABLE', ($module_data == 'users') ? NV_USERS_GLOBALTABLE : $db_config['prefix'] . '_' . $module_data);

// Xác định cấu hình module
$global_users_config = [];
$cacheFile = NV_LANG_DATA . '_' . $module_data . '_config_' . NV_CACHE_PREFIX . '.cache';
$cacheTTL = 3600;
if (($cache = $nv_Cache->getItem($module_name, $cacheFile, $cacheTTL)) != false) {
    $global_users_config = unserialize($cache);
} else {
    $sql = "SELECT config, content FROM " . NV_MOD_TABLE . "_config";
    $result = $db->query($sql);
    while ($row = $result->fetch()) {
        $global_users_config[$row['config']] = $row['content'];
    }
    $cache = serialize($global_users_config);
    $nv_Cache->setItem($module_name, $cacheFile, $cache, $cacheTTL);
}

require NV_ROOTDIR . '/modules/' . $module_file . '/global.functions.php';

$array_systemfield_cfg = [
    'first_name' => [0, 100],
    'last_name' => [0, 100],
    'question' => [3, 255],
    'answer' => [3, 255],
    'sig' => [0, 1000]
];

/**
 * @return mixed[]
 */
function nv_get_users_field_config()
{
    global $db;
    $array_field_config = [];
    $result_field = $db->query('SELECT * FROM ' . NV_MOD_TABLE . '_field ORDER BY weight ASC');
    while ($row_field = $result_field->fetch()) {
        $language = unserialize($row_field['language']);
        $row_field['title'] = (isset($language[NV_LANG_DATA])) ? $language[NV_LANG_DATA][0] : $row_field['field'];
        $row_field['description'] = (isset($language[NV_LANG_DATA])) ? nv_htmlspecialchars($language[NV_LANG_DATA][1]) : '';
        if (!empty($row_field['field_choices'])) {
            $row_field['field_choices'] = unserialize($row_field['field_choices']);
        } elseif (!empty($row_field['sql_choices'])) {
            $row_field['sql_choices'] = explode('|', $row_field['sql_choices']);
            $row_field['field_choices'] = [];
            $query = 'SELECT ' . $row_field['sql_choices'][2] . ', ' . $row_field['sql_choices'][3] . ' FROM ' . $row_field['sql_choices'][1];
            if (!empty($row_field['sql_choices'][4]) and !empty($row_field['sql_choices'][5])) {
                $query .= ' ORDER BY ' . $row_field['sql_choices'][4] . ' ' . $row_field['sql_choices'][5];
            }
            $result = $db->query($query);
            $weight = 0;
            while (list ($key, $val) = $result->fetch(3)) {
                $row_field['field_choices'][$key] = $val;
            }
        }
        $array_field_config[$row_field['field']] = $row_field;
    }
    return $array_field_config;
}