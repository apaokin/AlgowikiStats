<?php
class SpecialAlgowikiStats extends SpecialPage {
  var $dbr;
  var $counter;
	var $counterEn;
	var $output;


  function __construct() {
    parent::__construct( 'AlgowikiStats' );
  }

  function getGroupName() {
  	return 'wiki';
  }

  function tr($title, $value)
  {
    $i18nTitle = $this->msg($title);
    $info = "  <tr> <td> $i18nTitle </td>
     <td> $value </td> </tr>  ";
     return $info;

  }

	function par($inp)
	{
		$this->output.=('<tr> <td>'.$inp.'</td><td></td></tr>' );
	}

  function countUsersWithEdits()
  {
    return $this->dbr->query("SELECT COUNT(*) AS count_p FROM user WHERE user_editcount>0")->fetchRow()['count_p'];
  }

  function displayUsersInfo()
  {
    $info = $this->counter->users();
    $this->output.= " <table style='width:50%'> ";
    $this->par('<h3>'.$this->msg('algowikistats-users').'</h3>');
    $this->output.= $this->tr('algowikistats-users-count',$info);
    $this->output.= $this->tr('algowikistats-users-counteditors',$this->countUsersWithEdits());

  }

  function displayArticlesInfo()
  {
		$this->par('<h3>'.$this->msg('algowikistats-articles').'</h3>');

    $pages = $this->counter->pages();
    $tr = $this->tr('algowikistats-pages-count',$pages);
		$this->output.= $tr;

    $pages = $this->pagesCategory($this->dbr,true);
    $tr = $this->tr('algowikistats-pages-with-cat',$pages);
    $this->output.= $tr;

    $pages = $this->pagesCategory($this->dbr,false);
    $tr = $this->tr('algowikistats-pages-without-cat',$pages);
    $this->output.= $tr;


		$this->par('<h4>'.$this->msg('algowikistats-completeness').'</h4>');

    $startedArticles = $this->displayArticlesCountByCategory('Начатые_статьи');
    $compTr = $this->tr('algowikistats-articles-started',$startedArticles);

    $inProccessArticles = $this->displayArticlesCountByCategory('Статьи_в_работе');
    $compTr .= $this->tr('algowikistats-articles-in-proccess',$inProccessArticles);
    $finishedArticles = $this->displayArticlesCountByCategory('Законченные_статьи');
    $compTr .= $this->tr('algowikistats-articles-fininshed',$finishedArticles);

		$this->output .= $compTr;

		$this->par('<h4>'.$this->msg('algowikistats-levels').'</h4>');

		$aArticles = $this->displayArticlesCountByCategory('Уровень_алгоритма');
    $compTr = $this->tr('algowikistats-articles-a',$aArticles);

		$tArticles = $this->displayArticlesCountByCategory('Уровень_задачи');
    $compTr .= $this->tr('algowikistats-articles-t',$tArticles);

		$mArticles = $this->displayArticlesCountByCategory('Уровень_метода');
    $compTr .= $this->tr('algowikistats-articles-m',$mArticles);
		$this->output.= $compTr;
  }

	function displayEngArticlesInfo()
  {
		// $this->output.= " <table wikitable mw-statistics-table>";
		$this->par('<h3>'.$this->msg('algowikistats-articles-en').'</h3>');

    $pages = $this->counterEn->pages();
    $tr = $this->tr('algowikistats-pages-count',$pages);
    $this->output.= $tr;

    $pages = $this->pagesCategory($this->dbrEn,true);
    $tr = $this->tr('algowikistats-pages-with-cat',$pages);
    $this->output.= $tr;

    $pages = $this->pagesCategory($this->dbrEn,false);
    $tr = $this->tr('algowikistats-pages-without-cat',$pages);
    $this->output.= $tr;



		$this->par('<h4>'.$this->msg('algowikistats-completeness').'</h4>');

    $startedArticles = $this->displayEngArticlesCountByCategory('Started_articles');
    $compTr = $this->tr('algowikistats-articles-started',$startedArticles);

    $inProccessArticles = $this->displayEngArticlesCountByCategory('Articles_in_progress');
    $compTr .= $this->tr('algowikistats-articles-in-proccess',$inProccessArticles);
    $finishedArticles = $this->displayEngArticlesCountByCategory('Finished_articles');
    $compTr .= $this->tr('algowikistats-articles-fininshed',$finishedArticles);
		$this->output .= $compTr;

		$this->par('<h4>'.$this->msg('algowikistats-levels').'</h4>');

		$aArticles = $this->displayEngArticlesCountByCategory('Algorithm_level');
    $compTr = $this->tr('algowikistats-articles-a',$aArticles);

		$tArticles = $this->displayEngArticlesCountByCategory('Problem_level');
    $compTr .= $this->tr('algowikistats-articles-t',$tArticles);

		$mArticles = $this->displayEngArticlesCountByCategory('Method_level');
    $compTr .= $this->tr('algowikistats-articles-m',$mArticles);
		$this->output.= " $compTr </table>  ";
  }



  function displayArticlesCountByCategory($category)
  {
    return $this->dbr->query("SELECT COUNT(DISTINCT page_id) AS count_p FROM `page` INNER JOIN `categorylinks` ON
    ((cl_from = page_id))
     WHERE cl_to = '$category' AND cl_type IN  ('file','subcat','page')")->fetchRow()['count_p'];
  }

	function displayEngArticlesCountByCategory($category)
  {
    return $this->dbrEn->query("SELECT COUNT(DISTINCT page_id) AS count_p FROM `page` INNER JOIN `categorylinks` ON
    ((cl_from = page_id))
     WHERE cl_to = '$category' AND cl_type IN  ('file','subcat','page')")->fetchRow()['count_p'];
  }

  function pagesCategory($database,$with=true)
  {
    if($with)
    {
        $q = $database->query("SELECT COUNT(DISTINCT page_id) AS count_p FROM `page`
                    LEFT  JOIN `categorylinks` ON     ((cl_from = page_id))
                    WHERE cl_from IS NOT NULL");
    }
    else{
      $q = $database->query("SELECT COUNT(DISTINCT page_id) AS count_p FROM `page`
                  LEFT  JOIN `categorylinks` ON     ((cl_from = page_id))
                  WHERE cl_from IS  NULL");
    }
    return $q->fetchRow()['count_p'];

  }


  function execute( $par ) {
    $request = $this->getRequest();
    $this->setHeaders();
		$this ->dbr = wfGetDB( DB_SLAVE);
    $this ->dbrEn = wfGetDB( DB_SLAVE,[],'algowiki_en' );
    $this -> counter = new SiteStatsInit($this -> dbr );
		$this -> counterEn = new SiteStatsInit($this -> dbrEn );


		$this -> output= '';
		$this->displayUsersInfo();
    $this->displayArticlesInfo();
		$this->displayEngArticlesInfo();
		$this->getOutput()->addWikiText($this->output);

  }
}
