<?php

/*
 * TODO: extend list of general categories
 */

// list general categories
function listCategories() {
    // general categories (NEEDS EXTENSION)
    $categories = array('', '3D', 'abstract', 'actors', 'animals',
        'anime', 'manga', 'army', 'aviation', 'backgrounds', 'bikes', 'cars',
        'flowers', 'cinema', 'colors', 'games', 'girls', 'landscapes', 'men',
        'minimalistic', 'movies', 'music', 'nature', 'other', 'people',
        'photos', 'plants', 'portraits', 'psychedelic', 'military', 'brands',
        'food', 'drinks', 'celebrities', 'cartoons', 'Christmas', 'city',
        'comedy', 'funny', 'comics', 'computers', 'creative', 'designer',
        'wallpapers', 'drawings', 'fantasy', 'interior', 'exterior',
        'architecture', 'love', 'miscellaneous', 'beer', 'money', 'mood',
        'motorcycles', 'PDA', 'photoshop', 'ships', 'astrology', 'space',
        'astronomy', 'style', 'fonts', 'textures', 'patterns', 'fractals',
        'sport', 'personal', 'public', 'weapons', 'widescreen');
    // sort the list alphabetically
    sort($categories);
    $list = '';
    foreach ($categories as $cat) {
        $list .= "<option value=\"$cat\">$cat</option>";
    }
    return $list;
}

?>
