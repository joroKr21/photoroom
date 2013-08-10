<?php

/*
 * function to paginate a result set
 */
// resuts per page
define('PERPAGE', 20);

function paginate($target, $current, $pages, $adjacents = 3) {
    // validate input
    if ($pages <= 1 or $adjacents < 1) {
        return '';
    }
    // current page
    $current = (isset($current) and $current > 0 and $current <= $pages) ? $current : 1;

    if (preg_match('/\?/', $target)) {
        $target .= '&page';
    } else {
        $target .= '?page';
    }
    /* Setup page vars for display. */
    $prev = $current - 1;       // previous page is page - 1
    $next = $current + 1;       // next page is page + 1
    $lastpage = $pages;         // lastpage is = total pages
    $lpm1 = $lastpage - 1;      // last page minus 1
    /*
     * Now we apply our rules and draw the pagination object.
     * We're actually saving the code to a variable in case we want to draw it more than once.
     */
    $pagination = '';

    if ($lastpage > 1) {
        $pagination .= '<div class="pagination"><hr/>';
        // previous button
        if ($current > 1) {
            $pagination .= "<a href=\"$target=$prev\" title=\"Previous\">&lt;&lt;</a>";
        } else {
            $pagination .= '<span class="disabledPage">&lt;&lt;</span>';
        }
        // pages
        if ($lastpage <= 5 + ($adjacents * 2)) { // not enough pages to bother breaking it up
            for ($counter = 1; $counter <= $lastpage; $counter++) {
                if ($counter == $current) {
                    $pagination .= "<span class=\"currentPage\">$counter</span>";
                } else {
                    $pagination .= "<a href=\"$target=$counter\">$counter</a>";
                }
            }
        } else if ($lastpage > 5 + ($adjacents * 2)) { // enough pages to hide some
            // close to beginning; only hide later pages
            if ($current < 1 + ($adjacents * 2)) {
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                    if ($counter == $current) {
                        $pagination .= "<span class=\"currentPage\">$counter</span>";
                    } else {
                        $pagination .= "<a href=\"$target=$counter\">$counter</a>";
                    }
                }

                $pagination .= ". . .<a href=\"$target=$lpm1\">$lpm1</a><a href=\"$target=$lastpage\">$lastpage</a>";
            }
            // in middle; hide some front and some back
            elseif ($lastpage - ($adjacents * 2) > $current and $current > ($adjacents * 2)) {
                $pagination .= "<a href=\"$target=1\">1</a><a href=\"$target=2\">2</a>. . .";

                for ($counter = $current - $adjacents; $counter <= $current + $adjacents; $counter++) {
                    if ($counter == $current) {
                        $pagination .= "<span class=\"currentPage\">$counter</span>";
                    } else {
                        $pagination .= "<a href=\"$target=$counter\">$counter</a>";
                    }
                }

                $pagination .= ". . .<a href=\"$target=$lpm1\">$lpm1</a><a href=\"$target=$lastpage\">$lastpage</a>";
            }
            // close to end; only hide early pages
            else {
                $pagination .= "<a href=\"$target=1\">1</a><a href=\"$target=2\">2</a>. . .";

                for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                    if ($counter == $current) {
                        $pagination .= "<span class=\"currentPage\">$counter</span>";
                    } else {
                        $pagination .= "<a href=\"$target=$counter\">$counter</a>";
                    }
                }
            }
        }
        // next button
        if ($current < $lastpage) {
            $pagination .= "<a href=\"$target=$next\" title=\"Next\">&gt;&gt;</a>";
        } else {
            $pagination .= '<span class="disabledPage">&gt;&gt;</span>';
        }

        $pagination .= '<hr/></div>';
    }

    return $pagination;
}

?>