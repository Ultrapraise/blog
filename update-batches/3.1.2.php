<?php

use Illuminate\Support\Facades\DB;

$module = 'webed-blog';

acl_permission()
    ->registerPermission('View tags', 'view-tags', $module)
    ->registerPermission('Create tags', 'create-tags', $module)
    ->registerPermission('Update tags', 'update-tags', $module)
    ->registerPermission('Delete tags', 'delete-tags', $module);

DB::statement('ALTER TABLE blog_tags DROP COLUMN description');