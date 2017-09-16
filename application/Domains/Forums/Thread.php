<?php namespace App\Domains\Forums;

use App\Domains\Posts\Post;
use App\Domains\Posts\PostModel;
use CodeIgniter\I18n\Time;
use Myth\ORM\Entity;

/**
 * A Thread holds many posts around a single topic (hopefully!).
 * It is contained by a forum, and tracks view count and post counts.
 *
 * Generated by Vulcan at 2017-06-27 16:47:pm
 */
class Thread extends Entity
{
	protected $id;
	protected $user_id;
	protected $forum_id;
	protected $title;
	protected $first_post;
	protected $last_post;
	protected $view_count;
	protected $post_count;
	protected $created_at;
	protected $updated_at;
	protected $deleted_at;

	/**
	 * A collection of posts, as filled
	 * in by the Manager class.
	 *
	 * @var array
	 */
	public $posts = [];

	/**
	 * @var \App\Domains\Posts\Post
	 */
	protected $firstPostCache;

	/**
	 * @var Post
	 */
	protected $lastPostCache;

	/**
	 * @var \App\Domains\Users\User
	 */
	protected $user;

	/**
	 * @var PostModel
	 */
	protected $postModel;

	protected $_options = [
		'datamap' => [],
		'dates'   => ['created_at', 'updated_at', 'deleted_at'],
		'casts'   => [],
	];

	/**
	 * Sets the PostModel to use.
	 *
	 * @param \App\Domains\Posts\PostModel $model
	 *
	 * @return $this
	 */
	public function setPostModel(PostModel &$model)
	{
		$this->postModel = $model;

		return $this;
	}

	protected function ensurePostModel()
	{
		if (! $this->postModel instanceof PostModel)
		{
			$this->postModel = new PostModel();
		}
	}


	/**
	 * Generates a link to the thread-specific page.
	 *
	 * @return \CodeIgniter\Router\string|string
	 */
	public function link()
	{
		$slug = $this->id.'-'.strtolower(url_title($this->title));

		return route_to('threadLink', $slug);
	}

	/**
	 * Returns the first post for this thread.
	 *
	 * @return \App\Domains\Posts\Post|array|null|object
	 */
	public function firstPost()
	{
		if (! $this->firstPostCache instanceof Post)
		{
			$this->ensurePostModel();
			$this->firstPostCache = $this->postModel->find($this->first_post);
			$this->firstPostCache = $this->postModel->fillUsers([$this->firstPostCache])[0];
		}

		return $this->firstPostCache;
	}

	/**
	 * Returns the last post for this thread.
	 *
	 * @return \App\Domains\Posts\Post|array|null|object
	 */
	public function lastPost()
	{
		if (! $this->lastPostCache instanceof Post)
		{
			$this->ensurePostModel();
			$this->lastPostCache = $this->postModel->find($this->last_post ?? $this->first_post);
			$this->lastPostCache = $this->postModel->fillUsers([$this->lastPostCache])[0];
		}

		return $this->lastPostCache;
	}

	/**
	 * Gets our posts in a paginated manner ready for display.
	 *
	 * @param int $perPage
	 */
	public function populatePosts(int $perPage = 20)
	{
		$this->ensurePostModel();

		$posts = $this->postModel->where('id !=', $this->first_post)
		                         ->where('thread_id', $this->id)
		                         ->orderBy('created_at', 'desc')
		                         ->paginate($perPage);

		if (is_array($posts) && count($posts))
		{
			$posts = $this->postModel->fillUsers($posts);
		}

		$this->posts = $posts;
	}


	/**
	 * Returns a paginated array of posts belonging to this thread.
	 *
	 * @param int $perPage
	 *
	 * @return array
	 */
	public function posts(int $perPage = 20)
	{
		if (empty($this->posts))
		{
			$this->populatePosts($perPage);
		}

		return $this->posts;
	}

	/**
	 * Returns the summary line displayed under a thread
	 * that shows either who started the thread, or who posted last.
	 *
	 * @return string
	 */
	public function userSummaryLine()
	{
		$summary = '';

		if (empty($this->last_post)) return $summary;

		// Only 1 post?
		if ($this->last_post === $this->first_post)
		{
			$date    = Time::parse($this->created_at);
			$summary = lang('Threads.userSummarySame', [
				'date' => $date->humanize(),
				'link' => $this->user->link(),
				'user' =>$this->user->username]
			);
		}
		// Multiple posts..
		else
		{
			$date    = Time::parse($this->lastPost()->created_at);
			$summary = lang('Threads.userSummaryDifferent', [
				'link' => $this->lastPost()->user->link(),
				'user' => $this->lastPost()->user->username,
				'date' => $date->humanize()
			]);
			$summary = "<a href='{$this->lastPost()->user->link()}'><i class=\"fa fa-reply\" aria-hidden=\"true\"></i>{$this->lastPost()->user->username}</a> replied {$date->humanize()}";
		}

		return $summary;
	}


}
