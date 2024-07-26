<?php

declare(strict_types=1);

namespace App\UI\Post;

use Nette;
use Nette\Application\UI\Form;


final class PostPresenter extends Nette\Application\UI\Presenter
{
    

    public function __construct(
		private Nette\Database\Explorer $database,
	) {
	}

    public function renderDefault() : void
    {
        $section = $this->getSession('userData');
        $section->remove('userData');
       
    }
    
   
    //Vytvoření formuláře pro vytvoření poptávky
    public function createComponentSurveyForm() : Form
    {
        $SurveyForm = new Form;
        $SurveyForm->onRender[] = function($form) {
            $renderer = $form->getRenderer();
            $renderer->wrappers['controls']['container'] = null;
            $renderer->wrappers['pair']['container'] = 'div class="mb-3 row"';
            $renderer->wrappers['label']['container'] = 'div class="col-sm-3 col-form-label"';
            $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
            $renderer->wrappers['control']['description'] = 'span class=form-text';
            $renderer->wrappers['control']['errorcontainer'] = 'span class=invalid-feedback';
            $renderer->wrappers['control']['.error'] = 'is-invalid';
            $renderer->wrappers['error']['container'] = 'div class="alert alert-danger"';

            foreach ($form->getControls() as $control) {
                $type = $control->getOption('type');
                if ($type === 'button') {
                    $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-secondary');
                    $usedPrimary = true;

                } elseif (in_array($type, ['text', 'textarea', 'select', 'datetime', 'file'], true)) {
                    $control->getControlPrototype()->addClass('form-control');

                } elseif (in_array($type, ['checkbox', 'radio'], true)) {
                    if ($control instanceof Nette\Forms\Controls\Checkbox) {
                        $control->getLabelPrototype()->addClass('form-check-label');
                    } else {
                        $control->getItemLabelPrototype()->addClass('form-check-label');
                    }
                    $control->getControlPrototype()->addClass('form-check-input');
                    $control->getContainerPrototype()->setName('div')->addClass('form-check');

                } elseif ($type === 'color') {
                    $control->getControlPrototype()->addClass('form-control form-control-color');
                }
            }
        };
        $SurveyForm->addText('name', 'Jméno ')->setRequired('Zadejte prosím jméno')
            ->setHtmlAttribute('class','form-control');
        $SurveyForm->addTextArea('message','Zpráva:')
            ->setHtmlAttribute('placeholder','Zpráva...')
            ->setHtmlAttribute('class','form-control');
        $SurveyForm->addSelect('city', 'Město', [
                'Bolatice' => 'Bolatice','DB' => 'Dolní benešov', 'Hlucin' => 'Hlučín'
            ]);
        $SurveyForm->addText('address', 'Adresa ')->setRequired('Zadejte prosím adresu')
            ->setHtmlAttribute('class','form-control');
        $SurveyForm->addRadioList('importance', 'Důležitost:', [
            'rush' => 'spěchá',
            'norush' => 'nespěchá',
            ])->setRequired('Zadejte prosím důležitost');
        $SurveyForm->addCheckboxList('pet', 'Mazlíček:', [
            'dog' => 'Dog',
            'cat' => 'Kočka',
            ]);
        $services = [
            "cleaning" => "Úklid domácnosti",
            "gardening" => "Péče o zahradu",
            "computerHelp" => "Počítačová pomoc",
            "tutoring" => "Doučování",
            ];
        $SurveyForm->addMultiSelect('services', 'Služba:', $services);
        $SurveyForm->addInteger('cost','Odměna');
        $SurveyForm->addSubmit('send', 'Vložit poptávku');
        


        $SurveyForm->onSuccess[] = [$this, 'formSucceeded'];
        return $SurveyForm;
    }

    //Uložení do databáze
    public function formSucceeded(Form $form, $data): void
    {
        $data->services = implode(',',$data->services);
        $data->pet = implode(',',$data->pet);

        $this->database->table('posts')->insert($data);

        $this->forward('Post:default');


    }

    

   
    
}
