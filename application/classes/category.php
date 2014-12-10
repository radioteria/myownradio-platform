<?php
class category
{
    private $category = NULL;
    private $catdata = NULL;
    
    function __construct($category)
    {
        $this->catdata = db::query_single_row("SELECT * FROM `r_categories` WHERE `id` = :id OR `permalink` = :id LIMIT 1", array(':id' => $category));
        $this->category = $this->catdata['id'];
    }
    function exists()
    {
        return is_array($this->catdata);
    }
    function getStreams($from = 0, $limit = 1000)
    {
        return db::query("SELECT * FROM `r_streams` WHERE `category` = ? AND `status` = 1 LIMIT $from, $limit", array($this->category));
    }
    function getName() 
    {
        return $this->catdata['name'];
    }
    function getPermalink() 
    {
        return $this->catdata['permalink'];
    }
    function getId() 
    {
        return $this->catdata['id'];
    }
    static function getAllCategories()
    {
        return db::query("SELECT * FROM `r_categories` WHERE 1");
    }
}
