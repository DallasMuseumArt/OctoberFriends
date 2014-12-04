<?php

namespace DMA\Friends\Wordpress;

use DMA\Friends\Wordpress\Post;
use DMA\Friends\Models\Category;

class Taxonomy extends Post {

    /**
     * Import taxonomy terms from wordpress
     *
     * @param int $limit
     * The amount of records to import at one time
     *
     * @return int $count
     * number of records imported
     */
    public function import($limit = 0)
    {
        $count = 0;

        $terms = $this->db->table('wp_terms')
            ->join('wp_term_taxonomy', 'wp_terms.term_id', '=', 'wp_term_taxonomy.term_id')
            ->select('wp_terms.term_id', 'wp_terms.name', 'wp_term_taxonomy.taxonomy', 'wp_term_taxonomy.description')
            ->where('wp_term_taxonomy.taxonomy', 'LIKE', 'activity%')
            ->get();

        foreach($terms as $term) {
            $newTerm = Category::where('name', '=', $term->name)->first();
            if (!$newTerm) {
                $category               = new Category;
                $category->id           = $term->term_id;
                $category->name         = $term->name;
                $category->description  = $term->description;
                $category->save();
                $count++;
            }
        }

        return $count;
    }
}