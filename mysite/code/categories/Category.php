<?php

class Category extends Page {

   private static $db = array(
   		//THEMABLE
   		'MainColor' => 'Varchar(7)',
   		'SecondaryColor' => 'Varchar(7)',
   		'FeaturedHome' => 'Boolean',
   		'FeaturedOrder' => 'Int'
   	);

	private static $has_one = array(
	);
	
	private static $many_many = array(
		'Projects' => 'Project',
		'Brands' => 'Brand'
	);
	
	private static $belongs_many_many = array(
		'ProjectImage' => 'ProjectImage'
	);
	
	private static $defaults = array(
	);
		
//	private static $allowed_children = array("none");
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
//		$fields->addFieldToTab("Root.Content", new TextareaField("BlockList", "Fan item blocklist"));
		$fields->addFieldToTab("Root.Theme", new ColorField("MainColor","Main color"));
		$fields->addFieldToTab("Root.Theme", new ColorField("SecondaryColor","Secondary color"));
		
		if(Permission::check("ADMIN")) {
			$fields->addFieldToTab("Root.Featured", new CheckboxField("FeaturedHome","Feature on homepage"));
			$fields->addFieldToTab("Root.Featured", new TextField("FeaturedOrder","Order of featuring"));
		}
		
		$if=UploadField::create("BackgroundImage","Upload background image");
		$if->setFolderName("images/backgrounds");
		$fields->addFieldToTab("Root.Theme", $if);
		
		return $fields;
	}
	
	public function getFeaturedProjects($n=6) {
		$fp=$this->Projects(); //->find("Featured","1");
		if($fp) {
			return $fp->limit($n,0);
		}
	}
	
	public function ProjectsExcept($id = 0) {
		return Project::get()->where("Project.ID!=$id AND Category_Projects.CategoryID = $this->ID")->sort("Created DESC")->leftJoin("Category_Projects","Category_Projects.ProjectID = Project.ID")->limit(6);
	}
		
}
 
class Category_Controller extends Page_Controller {
	
	private static $allowed_actions = array (
		"index",
		"all"
	);
	
	public function init() {
		parent::init();
	}
	
	public function all() {
		$title = "Photo Galleries in ".$this->Title;
		return array(
			'Title' => $title,
			'MetaTitle' => $title,
			'Projects' => $this->Projects()
		);
	}
	
	public function ForSaleProjects() {
		$results = new ArrayList();
		$projects = Project::get()->where("Project.ForSale=1 AND Sold!=1 AND Category_Projects.CategoryID = $this->ID")->leftJoin("Category_Projects", "Category_Projects.ProjectID = Project.ID")->limit(6);
		$c=0;
		if($projects) {
			$c = $projects->Count();
			foreach($projects as $pt) {
				$results->push($pt);
			}
		}
		if($c<6) {
			$p = Project::get()->where("Project.ForSale=1 AND Category_Projects.CategoryID != $this->ID")->sort("RAND()")->leftJoin("Category_Projects", "Category_Projects.ProjectID = Project.ID")->limit(6-$c);
			if($p) {
				foreach($p as $pt) {
					$results->push($pt);
				}
			}
		}
		return $results;
	}
	
	public function getProjects($n=10) {
		if($this->Projects()) {
			return $this->Projects()->limit($n,0);
		}
	}
	
	public function getBrands() {
		return $this->Brands();
	}
		
}