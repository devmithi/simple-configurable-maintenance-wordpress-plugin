<?php if ( ! defined( 'ABSPATH' ) ) exit;?>
<style>
div.show-kmithi-maintenance{position: fixed;top: 0;bottom: 0;left: 0;right: 0;text-align: center;width: 100%;background: #888888;color: #FFFF;z-index: 999999; opacity: 0.75;}
div.show-kmithi-maintenance-body{background: #ffffff;color: #ff9800; margin: 30px; height: 100px;border-radius: 5px;opacity: 1 !important;padding-top: 3%;text-align: center;vertical-align: bottom;position: relative;}
</style>
<div class="show-kmithi-maintenance">
    <div class="show-kmithi-maintenance-body"><?php echo apply_filters( 'km_maintenance_msg', get_option('kmithi-fld-message') ); ?></div>
</div>
