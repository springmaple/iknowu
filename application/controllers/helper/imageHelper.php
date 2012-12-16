<?php

function getFirstImage($id) {
    $db = Zend_Registry::get("db");

    $imageResult = $db->query("SELECT i.img 
                                FROM prodimg p, image i 
                                WHERE p.imgid = i.imgid AND p.pid = ?
                                LIMIT 1", array($id));
    $image = $imageResult->fetchAll();
    $img = empty($image[0]['img']) ? "default.jpg" : $image[0]['img'];
    return $img;
}