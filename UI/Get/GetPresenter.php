<?php

declare(strict_types=1);

namespace App\UI\Get;

use Nette;
use App\Model\ServiceRepository;
use Nette\Application\UI\Form;


final class GetPresenter extends Nette\Application\UI\Presenter
{
    private $sort,$asc,$filteredData;
    public function __construct(
		private ServiceRepository $servicesRepository,
	) {
        $this->asc = true;
        $this->filteredData = [];
	}

    public function renderDefault(int $page = -1,$value = ''): void
    {
        //vytvoření session
        $section = $this->getSession('userData');


        $this->sort = $section->get('sort'); //nastavení hodnoty v session pro řazení dat

        if($page != -1)
            $section->set('page',$page); // stránky do session

        if($value != null)
             $this->sort = $value; // pomocná promněnná

        $this->asc = true; // natvrdo nastavené řazení

        //níže je podmínka pro vyhledávaní s uložením hodnot do session
        if($this->getHttpRequest()->getQuery('sortCol') != null && $this->getHttpRequest()->getQuery('searchField') != null)
        {
            $this->filteredData['sortCol'] = $this->getHttpRequest()->getQuery('sortCol');
            $this->filteredData['searchField'] = $this->getHttpRequest()->getQuery('searchField');
            $section->set('searchCol', $this->filteredData['sortCol']);
            $section->set('searchField', $this->filteredData['searchField']);
        }
        else
        {
            $this->filteredData = null;
            $section->remove('searchCol');
            $section->remove('searchField');

        }
        //použití hodnot
        $this->filteredData['sortCol'] = $section->get('searchCol');
        $this->filteredData['searchField'] = $section->get('searchField');

        //výpis z databáze pro vyfiltrování dat
        if(isset($this->filteredData['sortCol']))
        {
            $page = $section->get('page');
            $section->set('searchCol', $this->filteredData['sortCol']);
            $section->set('searchField', $this->filteredData['searchField']);
            $postsCount = $this->servicesRepository->getPublishedPostsFilterCount($this->filteredData['searchField'],$this->filteredData['sortCol']);
        
            $paginator = new Nette\Utils\Paginator;
            $paginator->setItemCount($postsCount);
            $paginator->setItemsPerPage(10); // staticky nastavná velikost stránky možné rozšíření na web
            $paginator->setPage($page); 

            $posts = $this->servicesRepository->findPublishedPostsFilter($paginator->getLength(), $paginator->getOffset(),$this->sort,$this->asc,$this->filteredData['searchField'],$this->filteredData['sortCol']);
            
            $this->template->sortCol = $this->filteredData['sortCol'];
            $this->template->searchField = $this->filteredData['searchField'];
        }
        else //výpis z databáze bez filtrování dat s možností řazení
        {
            $page = $section->get('page');
            $section->set('sort', $this->sort);
            

            $postsCount = $this->servicesRepository->getPublishedPostsCount();
        
            $paginator = new Nette\Utils\Paginator;
            $paginator->setItemCount($postsCount);
            $paginator->setItemsPerPage(10);  // staticky nastavná velikost stránky možné rozšíření na web
            $paginator->setPage($page); 
    
            $posts = $this->servicesRepository->findPublishedPosts($paginator->getLength(), $paginator->getOffset(),$this->sort,$this->asc);
    
        }


       
        //posílání potřebných dat do templatu dafult.latte
		$this->template->posts = $posts;
        $this->template->sortOrder = $this->sort;
        
		$this->template->paginator = $paginator;

    }

    public function handleOrder($value)
    {

        $this->sort = $value;
    }

    //funkce pro vytvoření formuláře pro filtraci dat
    protected function createComponentSearchForm(): Form
    {
        $form = new Form;
        $form->setMethod('GET');
        $searchValue = $form->addText('searchField')->setHtmlAttribute('placeholder', 'Stiskněte enter');
        $sortItem = $form->addSelect('sortCol', 'Řadit dle:', [
            'id' => 'ID',
            'message' => 'Popis objednávky',
            'address' => 'Adresa',
            'services' => 'Služby',
            'importance' => 'Důležitost',
            'pet' => 'Mazlíček',
            'cost' => 'Odměna',
            'created_at' => 'Vytvořeno',
          ])
          ->setHtmlAttribute('class', 'form-select form-select-sm');

        $form->onSuccess[] = [$this, 'formSearch'];

        return $form;
    }

    //funkce při odelsání formuláře enter
    public function formSearch(Form $form, $data): void
    {
        if($data->searchField == "")
        {
            $this->forward("Get:default");
        }
        else
        {
            $this->filteredData = $data;
            $this->forward("Get:default",['sortCol' => $data->sortCol,'searchField' => $data->searchField]);
        }
    }
}


