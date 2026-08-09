<?php
class Mona_Walker_Nav_Menu extends Walker_Nav_Menu {

    function start_lvl(&$output, $depth = 0, $args = array()) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class='menu-list'>\n";
    }

    function end_lvl(&$output, $depth = 0, $args = array()) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    function start_el( &$output, $item, $depth=0, $args=array(), $id = 0 ) {
        $object = $item->object;
        $type = $item->type;
        $title = $item->title;
        $description = $item->description;
        $permalink = $item->url;

        $output .= "<li class='parent fz16 fw6" .  implode(" ", $item->classes) . "'>";

        //Add SPAN if no Permalink
        if ( $permalink && $permalink != '#' ) {
            $output .= '<a class="menu-link" href="' . $permalink . '">';
        } else {
            $output .= '<a class="menu-link" href="javascript:;">';
        }

        $output .= $title;

        if ( $permalink && $permalink != '#' ) {
            $output .= '</a>';
        } else {
            $output .= '</a>';
        }

        if ( $args->walker->has_children ) {
            $output .= '<i class="bx bxs-chevron-down"></i>';
        }

    }

}