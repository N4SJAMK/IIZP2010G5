<?php
require_once 'functions.php';
require_once 'config.php';

class TeamboardAPI {
	private $connection;
	private $db;

	function __construct() {
		$this->connection = new Mongo(sprintf('mongodb://%s:%d/%s', DB_HOST, DB_PORT, DB_NAME),
			(defined('DB_USER') && defined('DB_PASS')) ? array('username' => DB_USER, 'password' => DB_PASS) : array());

		$this->db = $this->connection->selectDB(DB_NAME);

	}
	//for testing purposes, not actually used
	function addUser($email, $password) {
		$collection = $this->db->users;

		$document = array(
			'email' => $email,
			'password' => encryptPassword($password),
		);

		$collection->insert($document);
	}

	//for testing purposes, not actually used
	function addBoard($name, $width, $height, $background, $createdBy) {
		$collection = $this->db->boards;

		$document = array(
			'name' => $name,
			'size' => array('width' => $width, 'height' => $height),
			'background' => $background,
			'createdBy' => new MongoID($createdBy),
			'accessCode' => NULL,
		);

		$collection->insert($document);

		$this->addEvent($document['_id'], $createdBy, 'BOARD_CREATE');
	}

	//for testing purposes, not actually used
	function addTicket($board, $user, $content, $color, $x, $y, $z) {
		$collection = $this->db->tickets;

		$document = array(
			'board' => new MongoID($board),
			'content' => $content,
			'color' => $color,
			'position' => array('x' => $x, 'y' => $y, 'z' => $z),
		);

		$collection->insert($document);

		$this->addEvent($board, $user, 'TICKET_CREATE');
	}

	//for testing purposes, not actually used
	function addEvent($board, $user, $type) {
		$collection = $this->db->events;

		$document = array(
			'board' => new MongoID($board),
			'user' => array('id' => new MongoID($user), 'type' => 'user', 'username' => ''),
			'type' => $type,
			'createdAt' => new MongoDate());

		$collection->insert($document);
	}

	function removeUser($params = array()) {
		return $this->db->users->remove($params);
	}

	function removeBoard($params = array()) {
		return $this->db->boards->remove($params);
	}

	function getUserArray($params = array(), $skip, $limit) {
		$users = iterator_to_array($this->getUsers($params)->skip($skip)->limit($limit));

		if (empty($users)) {
			return $users;
		}

		$events = $this->getEvents();
		foreach ($events as $event) {
			$id = (string) $event['user']['id'];

			if (array_key_exists($id, $users)) {
				$users[$id]['events'][] = $event;
			}
		}

		foreach ($users as &$user) {
			$boardIds = iterator_to_array($this->getBoards(array('createdBy' => $user['_id']))->fields(array('_id' => true)));
			array_walk($boardIds, function (&$item, $key) {
				$item = array_key_exists('_id', $item) ? $item['_id'] : null;
			});

			$active = null;

			if (array_key_exists('events', $user)) {
				foreach ($user['events'] as $event) {
					if ($active == null) {
						$active = $event['createdAt'];
					} else {
						$active = $active->sec > $event['createdAt']->sec ? $active : $event['createdAt'];
					}
				}
			}

			$user['banned'] = array_key_exists('banned', $user) ? true : null;
			$user['boards'] = count($boardIds);
			$user['tickets'] = $this->getTicketCount(array('board' => array('$in' => $boardIds)));
			$user['active'] = $active;
			$user['active'] = isset($user['active']) ? $user['active']->toDateTime() : null;
		}

		return $users;
	}

	function getBoardArray($params = array(), $skip, $limit) {
		$start = microtime(true);

		$boards = iterator_to_array($this->getBoards($params)->skip($skip)->limit($limit));

		if (empty($boards)) {
			return $boards;
		}

		$events = $this->getEvents()->sort(array('createdAt' => 1));
		foreach ($events as $event) {
			$id = (string) $event['board'];

			if (array_key_exists($id, $boards)) {
				$boards[$id]['events'][] = $event;
			}
		}

		foreach ($boards as &$board) {

			$board['guests'] = 0;
			$board['shared'] = false;
			$board['active'] = null;

			if (array_key_exists('events', $board)) {
				foreach ($board['events'] as $event) {
					if ($board['active'] == null) {
						$board['active'] = $event['createdAt'];
					} else {
						$board['active'] = $board['active']->sec > $event['createdAt']->sec ? $board['active'] : $event['createdAt'];
					}

					switch ($event['type']) {
						case 'BOARD_CREATE':
							$board['createdAt'] = $event['createdAt'];
							break;
						case 'BOARD_PUBLISH':
							$board['shared'] = true;
							break;
						case 'BOARD_UNPUBLISH':
							$board['shared'] = false;
							$board['guests'] = 0;
							break;
						case 'BOARD_GUEST_JOIN':
							if ($board['shared']) {
								$board['guests'] += 1;
							}
					}

				}
			}

			$board['owner'] = $this->getUser(array('_id' => $board['createdBy']))['email'];
			$board['active'] = isset($board['active']) ? $board['active']->toDateTime() : null;
			$board['createdAt'] = isset($board['createdAt']) ? $board['createdAt']->toDateTime() : null;
			$board['tickets'] = $this->getTicketCount(array('board' => $board['_id']));
		}

		error_log(microtime(true) - $start);

		return $boards;
	}

	function getDatabaseSize($reserved = false) {
		return $this->db->execute('db.stats()')['retval'][$reserved ? 'fileSize' : 'dataSize'];
	}

	function getUser($params = array()) {
		return $this->db->users->findOne($params);
	}

	function getUsers($params = array()) {
		return $this->db->users->find($params);
	}

	function getEvents($params = array()) {
		return $this->db->events->find($params);
	}

	function getBoards($params = array()) {
		return $this->db->boards->find($params);
	}

	function getTickets($params = array()) {
		return $this->db->tickets->find($params);
	}

	function getUserCount($params = array()) {
		return $this->db->users->count($params);
	}

	function getEventCount($params = array()) {
		return $this->db->events->count($params);
	}

	function getBoardCount($params = array()) {
		return $this->db->boards->count($params);
	}

	function getTicketCount($params = array()) {
		return $this->db->tickets->count($params);
	}

	function getEvent($params = array()) {
		return $this->db->events->findOne($params);
	}

	function getActiveUserCount($from, $to) {
		return count($this->db->events->distinct(
			'user.id',
			array(
				'user.type' => 'user',
				'createdAt' => array(
					'$gt' => new MongoDate($from),
					'$lte' => new MongoDate($to),
				),
			)
		));
	}

	function getActiveBoardCount($from, $to) {
		return count($this->db->events->distinct(
			'board',
			array(
				'createdAt' => array(
					'$gt' => new MongoDate($from),
					'$lte' => new MongoDate($to),
				),
			)
		));
	}

	function getActiveGuestCount($from, $to) {
		return count($this->db->events->distinct(
			'user.id',
			array(
				'user.type' => 'guest',
				'createdAt' => array(
					'$gt' => new MongoDate($from),
					'$lte' => new MongoDate($to),
				),
			)
		));
	}

	function getNewTicketsCount($from, $to) {
		return $this->getEventCount(
			array(
				'type' => 'TICKET_CREATE',
				'createdAt' => array(
					'$gt' => new MongoDate($from),
					'$lte' => new MongoDate($to),
				),
			)
		);
	}

	function getNewBoardsCount($from, $to) {
		return $this->getEventCount(
			array(
				'type' => 'BOARD_CREATE',
				'createdAt' => array(
					'$gt' => new MongoDate($from),
					'$lte' => new MongoDate($to),
				),
			)
		);
	}

	function getUserLastActive($user) {
		$event = $this->getEvent(
			array(
				'$query' => array(
					'user.id' => new MongoID($user),
				),
				'$orderBy' => array(
					'createdAt' => -1,
				),
			)
		);

		if (isset($event)) {
			return $event['createdAt'];
		}

		return null;
	}

	function getBoardLastActive($board) {
		$event = $this->getEvent(
			array(
				'$query' => array(
					'board' => new MongoID($board),
				),
				'$orderBy' => array(
					'createdAt' => -1,
				),
			)
		);

		if (isset($event)) {
			return $event['createdAt'];
		}

		return null;
	}

	function unshareBoard($params = array()) {
		return $this->db->boards->update($params, array('$set' => array('accessCode' => null)));
	}

	function setUserData($criteria, $data) {
		return $this->db->users->update($criteria, array('$set' => $data));
	}

	function unsetUserData($criteria, $data) {
		return $this->db->users->update($criteria, array('$unset' => $data));
	}
};

$database = new TeamboardAPI();

?>
