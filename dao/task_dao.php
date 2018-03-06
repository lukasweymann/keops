<?php

require_once(DB_CONNECTION);
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . "/dto/task_dto.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . "/utils/utils.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/utils/datatables_helper.class.php' );

class task_dao {
  private $conn;
  public static $columns_project_tasks;
  public static $columns_user_tasks;
  
  public function __construct(){
    $this->conn = new keopsdb();
    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  
  function getTaskById($id) {
    try {
      $task = new task_dto();
      
      $query = $this->conn->prepare("SELECT * FROM tasks WHERE id = ?;");
      $query->bindParam(1, $id);
      $query->execute();
      $query->setFetchMode(PDO::FETCH_ASSOC);
      while($row = $query->fetch()){
        $task->id = $row['id'];
        $task->project_id = $row['project_id'];
        $task->assigned_user = $row['assigned_user'];
        $task->corpus_id = $row['corpus_id'];
        $task->size = $row['size'];
        $task->status = $row['status'];
        $task->creation_date = $row['creation_date'];
        $task->assigned_date = $row['assigned_date'];
        $task->completed_date = $row['completed_date'];
      }
      $this->conn->close_conn();
      return $task;
    } catch (Exception $ex) {
      $this->conn->close_conn();
      throw new Exception("Error in task_dao::getTaskById : " . $ex->getMessage());
    }
  }
  
  function insertTask($task_dto) {
    try {
      $query = $this->conn->prepare("INSERT INTO tasks (project_id, assigned_user, corpus_id, assigned_date) VALUES (?, ?, ?, ?);");
      $query->bindParam(1, $task_dto->project_id);
      $query->bindParam(2, $task_dto->assigned_user);
      $query->bindParam(3, $task_dto->corpus_id);      
      $query->bindParam(4, $task_dto->assigned_date);
      $query->execute();
      $task_dto->id = $this->conn->lastInsertId();
      $this->conn->close_conn();
      return true;
    } catch (Exception $ex) {
      $this->conn->close_conn();
      throw new Exception("Error in task_dao::insertTask : " . $ex->getMessage());
    }
    return false;
  }
  
  function updateTaskSize($task_id) {
    try {
      $query = $this->conn->prepare("with counted as (select count(task_id) as count from sentences_tasks where task_id = ? group by task_id) update tasks as t set size=s.count from counted as s where t.id = ?;");
      $query->bindParam(1, $task_id);
      $query->bindParam(2, $task_id);
      $query->execute();
      $this->conn->close_conn();
      return true;
    } catch (Exception $ex) {
      $this->conn->close_conn();
      throw new Exception("Error in task_dao::updateTaskSize : " . $ex->getMessage());
    }
    return false;
  }
  
  function closeTask($task_dto){
    try {
      $query = $this->conn->prepare("UPDATE TASKS set status='DONE', completed_date = ? where id = ?;");
      $query->bindParam(1, $task_dto->completed_date);
      $query->bindParam(2, $task_dto->id);
      $query->execute();
      $this->conn->close_conn();
      return true;
    } catch (Exception $ex) {
      $this->conn->close_conn();
      throw new Exception("Error in task_dao::closeTask : " . $ex->getMessage());
    }
    return false;
  }
  
  function startTask($task_id){
    try {
      $query = $this->conn->prepare("UPDATE TASKS set status='STARTED' where id = ?;");
      $query->bindParam(1, $task_id);
      $query->execute();
      $this->conn->close_conn();
      return true;
    } catch (Exception $ex) {
      $this->conn->close_conn();
      throw new Exception("Error in task_dao::startTask : " . $ex->getMessage());
    }
    return false;
  }
  
  function getDatatablesTasks($request) {
    try {
      return json_encode(DatatablesProcessing::complex( $request, $this->conn,
              "tasks as t left join projects as p on p.id = t.project_id left join users as u on u.id = t.assigned_user "
              . "left join sentences_tasks as st on t.id = st.task_id",
              "t.id",
              self::$columns_project_tasks,
              null,
              "project_id=" . $request['p_id'],
              ["t.id", "u.name", "p.id", "u.id"]));
    } catch (Exception $ex) {
      throw new Exception("Error in task_dao::getDatatablesTasks : " . $ex->getMessage());
    }
  }
  
  function getDatatablesUserTasks($request, $user_id) {
    try {
      return json_encode(DatatablesProcessing::complex( $request, $this->conn,
              "tasks as t "
              . "left join projects as p on p.id = t.project_id "
              . "left join users as u on u.id = t.assigned_user "
              . "left join langs as l1 on l1.id = p.source_lang "
              . "left join langs as l2 on l2.id = p.target_lang "
              . "left join users as us on us.id = p.owner "
              . "left join sentences_tasks as st on t.id = st.task_id",
              "t.id",
              self::$columns_user_tasks,
              null,
              "t.assigned_user=" . $user_id ,
              ["t.id", "p.name", "l1.langcode", "l2.langcode", "us.email"]));
    } catch (Exception $ex) {
      throw new Exception("Error in task_dao::getDatatablesUserTasks : " . $ex->getMessage());
    }
  }
}
task_dao::$columns_project_tasks = array(
    array( 'db' => 't.id', 'alias' => 'id', 'dt' => 0 ),
    array( 'db' => 'u.name', 'alias' => 'name', 'dt' => 1 ),
    array( 'db' => 'size', 'dt' => 2 ),
    array( 'db' => 't.status', 'alias' => 'status', 'dt' => 3 ),
    array( 'db' => 't.creation_date', 'alias' => 'creation_date', 'dt' => 4,
        'formatter' => function ($d, $row) { return getFormattedDate($d); } ),
    array( 'db' => 't.assigned_date', 'alias' => 'assigned_date', 'dt' => 5,
        'formatter' => function ($d, $row) { return getFormattedDate($d); } ),
    array( 'db' => 't.completed_date', 'alias' => 'completed_date', 'dt' => 6,
        'formatter' => function ($d, $row) { return getFormattedDate($d); } ),
    array( 'db' => 'p.id', 'alias' => 'p_id', 'dt' => 7 ),
    array( 'db' => 'u.id', 'alias' => 'u_id', 'dt' => 8),
    array( 'db' => "count(case when st.evaluation!='P' then 1 end)", 'alias' => 'completedsentences', 'dt' => 9)
);

task_dao::$columns_user_tasks = array(
    array( 'db' => 't.id', 'alias' => 'id', 'dt' => 0 ),
    array( 'db' => 'p.name', 'alias' => 'name', 'dt' => 1 ),
    array( 'db' => 'l1.langcode', 'alias' => 'source_lang', 'dt' => 2 ),
    array( 'db' => 'l2.langcode', 'alias' => 'target_lang', 'dt' => 3 ),
    array( 'db' => 'size', 'dt' => 4 ),
    array( 'db' => 't.status', 'alias' => 'status', 'dt' => 5 ),
    array( 'db' => 't.creation_date', 'alias' => 'creation_date', 'dt' => 6,
        'formatter' => function ($d, $row) { return getFormattedDate($d); } ),
    array( 'db' => "count(case when st.evaluation!='P' then 1 end)", 'alias' => 'sentencescompleted', 'dt' => 7),
    array( 'db' => 'us.email', 'alias' => 'email', 'dt' => 8 )
);