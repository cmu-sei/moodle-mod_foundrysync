<?php

namespace tool_foundrysync\oauth2;

class api extends \core\oauth2\api {

//public static function discover_endpoints($issuer) {}

    public static function create_issuer($data) {
        $issuer = new \core\oauth2\issuer(0, $data);

        // Will throw exceptions on validation failures.
        $issuer->create();

        // Perform service discovery.
        self::discover_endpoints($issuer);
        self::guess_image($issuer);
        return $issuer;
    }

    public static function update_issuer($data) {
        $issuer = new \core\oauth2\issuer(0, $data);

        // Will throw exceptions on validation failures.
        $issuer->update();

        // Perform service discovery.
        self::discover_endpoints($issuer);
        self::guess_image($issuer);
        return $issuer;
    }

    public static function delete_issuer($id) {
        $issuer = new \core\oauth2\issuer($id);

        $systemaccount = self::get_system_account($issuer);
        if ($systemaccount) {
            $systemaccount->delete();
        }
        $endpoints = self::get_endpoints($issuer);
        if ($endpoints) {
            foreach ($endpoints as $endpoint) {
                $endpoint->delete();
            }
        }

        // Will throw exceptions on validation failures.
        return $issuer->delete();
    }

    public static function guess_image($data) {
        if (empty($data->image) && !empty($data->baseurl)) {
            $baseurl = parse_url($data->baseurl);
            $imageurl = $baseurl['scheme'] . '://' . $baseurl['host'] . '/favicon.ico';
            $data->image = $imageurl;
        }
        return $data->image;
    }


}
