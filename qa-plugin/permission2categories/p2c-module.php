<?php
class p2c_category_permission 
{	
	/**
	 * 
	 * @var string - the meta-tag we insert into the title colunm 
	 */
	var $category_metakey = 'p2c_permission_level';
	
	/**
	 * @var array - Cache for the category permission levels
	 */
	var $category_permit_levels = array();
	var $category_permit_jenis = array();
	

	function __construct () 
	{
		$this->get_category_permit_levels();
		$this->get_category_permit_jenis();
	}
	
	
	/**
	 * If category is updated without error we add/edit our permission level into the qa_categorymetas table.
	 */
	function init_page() 
	{
		$permit_level = qa_post_text('p2c_permit_level');
		if ( qa_clicked('dosavecategory') && isset($permit_level) && !qa_clicked('docancel') ){
			$this->edit_permit_level(qa_post_text('edit'), $this->category_metakey, qa_post_text('p2c_permit_level'));
		}
	}
	
	
	/**
	 * Uses qa_db_categorymeta_set(...) to insert or edit our permission level into the qa_categorymetas table.
	 * 
	 * @see qa_db_categorymeta_set()
	 * @param string $categoryid - Category id
	 * @param string $key - Inserted into the title colunm.
	 * @param string $value - Inserted into the content colunm 
	 */
	function edit_permit_level($categoryid, $key, $value)
	{
		require_once QA_INCLUDE_DIR.'qa-db-metas.php'; //make sure we have access to the functions we need.
		
		qa_db_categorymeta_set($categoryid, $key, $value);
	}

	
	/**
	 * Retrives the permission levels for catagories from the qa_categorymetas table and sets up an associative array with 'category id => permission level'.
	 * 
	 * @return array - category id => permission level
	 */
	function get_category_permit_levels() 
	{		
		$category_permissions = qa_db_read_all_assoc(qa_db_query_sub('
				SELECT categoryid, content 
				FROM ^categorymetas 
				WHERE title=\''. $this->category_metakey .'\''));

		foreach ($category_permissions as $value)
			$this->category_permit_levels[$value['categoryid']] = $value['content'];
		
		return $this->category_permit_levels;
	}

	function get_category_permit_jenis() 
	{		
		$category_permissions = qa_db_read_all_assoc(qa_db_query_sub('
				SELECT categoryid, jenis 
				FROM ^categorymetas 
				WHERE title=\''. $this->category_metakey .'\''));

		foreach ($category_permissions as $value)
			$this->category_permit_jenis[$value['categoryid']] = $value['jenis'];
		
		return $this->category_permit_jenis;
	}
	
	
	/**
	 * Checks the permission level needed to access $categoryid. If no permission level exists returns 0. 
	 * 
	 * @param string $categoryid
	 * @return string - number which equates to the permission level required
	 */
	function category_permit_level($categoryid) 
	{
		$all_permit_levels = $this->category_permit_levels;

		if ( array_key_exists($categoryid, $all_permit_levels) )
			return $all_permit_levels[$categoryid];
		else 
			return 0;	
	}

	function category_permit_jenis($categoryid) 
	{
		$all_permit_jenis = $this->category_permit_jenis;

		if ( array_key_exists($categoryid, $all_permit_jenis) )
			return $all_permit_jenis[$categoryid];
		else 
			return 0;	
	}
	
	
	/**
	 * Returns true if the logged in user has the required permission level to access $categoryid else false
	 * 
	 * @param unknown_type $categoryid
	 * @return bool
	 */
	function has_permit($categoryid) 
	{
		$permit_level = $this->category_permit_level($categoryid);
		$permit_jenis = $this->category_permit_jenis($categoryid);
		// echo "<pre>" , print_r(qa_db_user_account_selectspec(qa_get_logged_in_userid(), true));
		// echo "<pre>" , print_r(qa_get_logged_in_userid());
		// echo "<pre>" , print_r(qa_get_logged_in_user_cache());
		// echo "<pre>" , print_r(qa_db_get_pending_result('loggedinuser', qa_db_user_account_selectspec(qa_get_logged_in_userid(), true)));
		$jenis_user = qa_get_logged_in_user_field('jenis');
		// die();

		// echo $jenis_user;die();
		// echo "<pre>" , print_r($permit_jenis);die();
		if ( qa_get_logged_in_level() >= $permit_level || $permit_level == 0 )
			return true;
		else
			return false;
	}
}