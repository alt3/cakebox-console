<?php
namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class SiteFileForm extends Form
{

	protected function _buildSchema(Schema $schema)
	{
		return $schema->addField('url', 'string')
		->addField('webroot', ['type' => 'string']);
	}

	protected function _buildValidator(Validator $validator)
	{
		return $validator
			->requirePresence('url')
			->requirePresence('webroot');
	}
}
