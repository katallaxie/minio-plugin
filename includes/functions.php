<?php

if ( ! function_exists( 'minio_get_attachment_url' ) ) {
    function minio_get_attachment_url( $id, $size = null ) {
        global $minio;

        return $minio->get_attachment_url( $id, $size );
    }
}