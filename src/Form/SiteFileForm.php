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
			->addField('webroot', ['type' => 'string'])
			->addField('force', ['type' => 'boolean']);
	}

	protected function _buildValidator(Validator $validator)
	{
		return $validator
			->requirePresence('url')
			// ->add('url', 'length', [
			// 	'rule' => ['minLength', 10],
			// 	'message' => 'A minimum of 10 characters is required'])
			// ->add('url', 'format', [
			// 	'rule' => 'email',
			// 	'message' => 'A valid email address is required',
			// 	])
			->requirePresence('webroot');
	}
}
