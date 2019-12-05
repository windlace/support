<?php

if (!function_exists('check_remote_image')) {

    /**
     * Checks if remote image has status code "200" and desired content-type.
     *
     * @param string $url
     * @param array $allowedImageTypes
     * @param int $maxLevel
     * @param int $currentLevel
     * @return bool
     */
    function check_remote_image($url, array $allowedImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_WEBP], $maxLevel = 5, $currentLevel = 0)
    {
        if ($currentLevel > $maxLevel) {
            return false;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        curl_exec($ch);

        $info = curl_getinfo($ch);

        curl_close($ch);

        if (isset($info['http_code'])) {
            switch ($info['http_code']) {
                case 200:
                    return in_array($info['content_type'], array_map('image_type_to_mime_type', $allowedImageTypes));
                case 301:
                case 302:
                    return isset($info['redirect_url']) ?
                        check_remote_image($info['redirect_url'], $allowedImageTypes, $maxLevel, $currentLevel + 1) : false;
                default:
                    return false;
            }
        }
        return false;
    }
}
