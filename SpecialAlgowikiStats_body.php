<?php
class SpecialAlgowikiStats extends SpecialPage {
	var $dbr;
	var $counter;


	function __construct() {
		parent::__construct( 'AlgowikiStats' );
	}

	function tr($title, $value)
	{
		$i18nTitle = $this->msg($title);
		$info = "  <tr> <td> $i18nTitle </td>
		 <td> $value </td> <tr>  ";
		 return $info;

	}

	function displayUsersInfo()
	{
		$output = $this->getOutput();

		$info = $this->counter->users();
		$tr = $this->tr('algowikistats-users-count',$info);
		$html = " <table style='width:50%'> $tr </table>  ";
		$output->addWikiText($html );
	}

	function displayArticlesInfo()
	{
		$output = $this->getOutput();
		$output->addWikiText("<h3> Статьи</h3>" );
		$articles = $this->counter->articles();
		$tr = $this->tr('algowikistats-articles-count',$articles);
		$html = " <table style='width:50%'> $tr </table>  ";
		$output->addWikiText($html );
		$output->addWikiText("<h4>Готовность статей</h4>" );


		$startedArticles = $this->displayArticlesCompletness('Начатые_статьи');
		$compTr = $this->tr('algowikistats-articles-started',$startedArticles);

		$inProccessArticles = $this->displayArticlesCompletness('Статьи_в_работе');
		$compTr .= $this->tr('algowikistats-articles-in-proccess',$inProccessArticles);

		$finishedArticles = $this->displayArticlesCompletness('Законченные_статьи');
		$compTr .= $this->tr('algowikistats-articles-fininshed',$finishedArticles);


		$compHtml = " <table style='width:50%'> $compTr </table>  ";
		$output->addWikiText($compHtml );



	}

	function displayArticlesCompletness($category)
	{
		// $config = MediaWikiServices::getInstance()->getMainConfig();
		$tables = array('page','categorylinks');
	  $conds = [
	       'page_namespace' => MWNamespace::getContentNamespaces(),
			   'page_is_redirect' => 0

	 	];
    // if ( $config->get( 'ArticleCountMethod' ) == 'link' ) {
    //     $tables[] = 'pagelinks';
    //     $conds[] = 'pl_from=page_id';
    // }

		$joinConds = array( 'categorylinks' => array( 'INNER JOIN', array(
		'cl_from=page_id', "cl_to='$category'") ) );

	   $res =  $this->dbr->select(
			 										$tables,
									         array('COUNT(DISTINCT page_id) AS count_pages'),
									         $conds,
									         __METHOD__,
													 array(),
													 $joinConds
									       );
		 foreach( $res as $row ) {
			 return $row->count_pages;
			}
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		$this ->dbr = wfGetDB( DB_REPLICA );
		$this -> counter = new SiteStatsInit($this -> dbr );
		$this->displayUsersInfo();
		$this->displayArticlesInfo();

	}
}
