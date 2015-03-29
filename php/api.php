<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'teamboard-dev');
define('DB_PORT', 27017);

class TeamboardAPI
{
	private $connection;
	private $db;

	function __construct()
	{
		$this->connection = new Mongo(sprintf('mongodb://%s:%d/%s', DB_HOST, DB_PORT, DB_NAME));

		$this->db = $this->connection->selectDB(DB_NAME);
	}

	function addUser($email, $password)
	{		
		$collection = $this->db->users;

		$document = array(
					'email' => $email,
					'password' => password_hash($password, PASSWORD_BCRYPT, array('cost' => 10)),
					);

		$collection->insert($document);
	}

	function addBoard($name, $width, $height, $background, $createdBy)
	{
		$collection = $this->db->boards;

		$document = array(
					'name' => $name,
					'size' => array('width' => $width, 'height' => $height),
					'background' => $background,
					'createdBy' => new MongoID($createdBy),
					'accessCode' => NULL
					);

		$collection->insert($document);

		$this->addEvent($document['_id'], $createdBy, 'BOARD_CREATE');
	}

	function addTicket($board, $user, $content, $color, $x, $y, $z)
	{
		$collection = $this->db->tickets;

		$document = array(
					'board' => new MongoID($board),
					'content' => $content,
					'color' => $color,
					'position' => array('x' => $x, 'y' => $y, 'z' => $z)
					);

		$collection->insert($document);

		$this->addEvent($board, $user, 'TICKET_CREATE');
	}

	function addEvent($board, $user, $type)
	{
		$collection = $this->db->events;

		$document = array(
					'board' => new MongoID($board),
					'user' => array('id' => new MongoID($user), 'type' => 'user', 'username' => ''),
					'type' => $type,
					'createdAt' => new MongoDate());

		$collection->insert($document);
	}

	function removeUser($params = array())
	{
		return $this->db->users->remove($params);
	}

	function removeBoard($params = array())
	{
		return $this->db->boards->remove($params);
	}	

	function getUser($params = array())
	{
		return $this->db->users->findOne($params);	
	}

	function getUsers($params = array())
	{
		return $this->db->users->find($params);	
	}

	function getEvents($params = array())
	{
		return $this->db->events->find($params);	
	}

	function getBoards($params = array())
	{
		return $this->db->boards->find($params);	
	}

	function getTickets($params = array())
	{
		return $this->db->tickets->find($params);	
	}

	function getUserCount($params = array())
	{
		return $this->db->users->count($params);	
	}

	function getEventCount($params = array())
	{
		return $this->db->events->count($params);	
	}

	function getBoardCount($params = array())
	{
		return $this->db->boards->count($params);	
	}

	function getTicketCount($params = array())
	{
		return $this->db->tickets->count($params);	
	}	

	function getEvent($params = array())
	{
		return $this->db->events->findOne($params);
	}

	function getActiveUserCount($from, $to)
	{
		return count($this->db->events->distinct(
			'user.id', 
			array( 
				'user.type' => 'user',
				'createdAt' => 
				array(
					'$gt' => new MongoDate($from), 
					'$lte' => new MongoDate($to)
					)
				)
			));
	}

	function getActiveBoardCount($from, $to)
	{
		return count($this->db->events->distinct(
			'board', 
			array( 
				'createdAt' => 
				array(
					'$gt' => new MongoDate($from), 
					'$lte' => new MongoDate($to)
					)
				)
			));
	}

	function getActiveGuestCount($from, $to)
	{
		return count($this->db->events->distinct(
			'user.id', 
			array( 
				'user.type' => 'guest',
				'createdAt' => 
				array(
					'$gt' => new MongoDate($from), 
					'$lte' => new MongoDate($to)
					)
				)
			));
	}

	function getNewTicketsCount($from, $to)
	{
		return $this->getEventCount(
			array( 
				'type' => 'TICKET_CREATE',
				'createdAt' => 
				array(
					'$gt' => new MongoDate($from), 
					'$lte' => new MongoDate($to)
					)
				)
			);
	}

	function getNewBoardsCount($from, $to)
	{
		return $this->getEventCount(
			array( 
				'type' => 'BOARD_CREATE',
				'createdAt' => 
				array(
					'$gt' => new MongoDate($from), 
					'$lte' => new MongoDate($to)
					)
				)
			);
	}	

	function getUserLastActive($user)
	{
		return $this->getEvent(
			array(				
				'$query' => array(
								'user.id' => new MongoID($user)
							),
				'$orderBy' => array(
								'createdAt' => -1
							)
			)
			);
	}

	function getBoardLastActive($board)
	{
		return $this->getEvent(
			array(				
				'$query' => array(
								'board' => new MongoID($board)
							),
				'$orderBy' => array(
								'createdAt' => -1
							)
			)
			);
	}

	function setUserData($criteria, $data)
	{
		$this->db->users->update($criteria, array('$set' => $data));
	}	
};

$database = new TeamboardAPI();

?>