<?php

declare(strict_types=1);

namespace AliBayat\LaravelCategorizable\Tests;

use AliBayat\LaravelCategorizable\Categorizable;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};

class Post extends Model
{
	use HasFactory;
	use Categorizable;
	
	/**
	 * @var string[]
	 */
	protected $fillable = ['title', 'body'];
	
	/**
	 * @var bool
	 */
	public $timestamps = false;
	
	/**
	 * @var string
	 */
	protected $table = 'posts';
}