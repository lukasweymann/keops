<?php
/**
 * Project services.
 * Currently "new", that creates a new project and redirects to the Projects tab,
 *  and "list_dt", that serves the datatables content of the Projects table
 */
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . "/resources/config.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/dao/user_langs_dao.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/dao/user_dao.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/dao/language_dao.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/utils/utils.php");
$PAGETYPE = "admin";
require_once(RESOURCES_PATH . "/session.php");

$service = filter_input(INPUT_GET, "service");
if ($service == "usersByLanguage") {
  $source_lang = filter_input(INPUT_GET, "source_lang");
  $target_lang = filter_input(INPUT_GET, "target_lang");

  if (isset($source_lang) && isset($target_lang)){
    $language_dao = new language_dao();

    $source_lang_object = $language_dao->getLangByLangCode($source_lang);
    $target_lang_object = $language_dao->getLangByLangCode($target_lang);

    $user_langs_dao = new user_langs_dao();
    $user_ids = $user_langs_dao->getUserIdsByLangPair($source_lang_object->id, $target_lang_object->id);
    if (!empty($user_ids)) {
        $user_dao = new user_dao();
        $users = $user_dao->getUsersByIds($user_ids);
    } else {
        $users = array();
    }

    echo json_encode(array("result" => 200, "data" => $users));
  } else {
    echo json_encode(array("result" => -1, "message" => "Missing parameters"));
  }
}