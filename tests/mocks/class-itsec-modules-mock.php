<?php

class ITSEC_Modules {
	public static $force_unique_nicename = false;
	public static function get_settings( $setting ) {
		return array(
			'force_unique_nicename' => self::$force_unique_nicename,
		);
	}
}
