<?php

namespace App\Model;

use Nette;

final class ServiceRepository
{
	public function __construct(
		private Nette\Database\Explorer $database,
	) {
	}

	public function fundAllServices(): Nette\Database\Table\Selection
	{
		return $this->database->table('posts')
			->where('created_at < ', new \DateTime)
			->order('created_at DESC');
	}

	
	//dotaz bez filtrace s otevřenou možností měnit pořadí výpisu
	public function findPublishedPosts(int $limit, int $offset,$sort,bool $asc): Nette\Database\ResultSet
	{
		if(!in_array($sort,['id','cost','services','city','address','pet','importance','name','created_at','message']))
		{
			$sort = 'created_at'; // pro případ že by uživatel chtěl zadávat vlastní parametry do url
		}
		return $this->database->query('
			SELECT * FROM posts
			WHERE created_at < ?
			ORDER BY '.$sort.' '.($asc ? 'ASC' : 'DESC' ).'
			LIMIT ?
			OFFSET ?',
			new \DateTime, $limit, $offset,
		);
	}



	public function getPublishedPostsCount(): int
	{
		return $this->database->fetchField('SELECT COUNT(*) FROM posts');
	}
  
	// Doraz s filtrací s otevřenou možností měnit pořadí výpisu
	public function findPublishedPostsFilter(int $limit, int $offset,$sort,bool $asc,$value, $col): Nette\Database\ResultSet
	{
		if(!in_array($sort,['id','cost','services','city','address','pet','importance','name','created_at','message']))
		{
			$sort = 'created_at'; // pro případ že by uživatel chtěl zadávat vlastní parametry do url
		}
		$value = '%'.$value.'%';
		return $this->database->query('
			SELECT * FROM posts
			WHERE created_at < ? AND '.$col.' LIKE ?
			ORDER BY '.$sort.' '.($asc ? 'ASC' : 'DESC' ).'
			LIMIT ?
			OFFSET ?',
			new \DateTime,$value, $limit, $offset,
		);
	}

	public function getPublishedPostsFilterCount($value, $col): int
	{
		$value = '%'.$value.'%';
		return $this->database->fetchField('SELECT COUNT(*) FROM posts WHERE '.$col.' LIKE ?',$value);
	}

}