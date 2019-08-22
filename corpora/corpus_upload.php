<?php
/**
 * Uploads a corpus
 */
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . "/resources/config.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/dao/user_dao.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/dao/corpus_dao.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/dao/sentence_dao.php");
$PAGETYPE = "admin";
require_once(RESOURCES_PATH . "/session.php");

class CorpusException extends Exception{ }

try {
  if (!empty($_FILES)) {
      $mode = filter_input(INPUT_POST, "mode");

      $tempFile = $_FILES['file']['tmp_name'];
//      error_log($tempFile);
//      error_log(implode(", ", $_FILES['file']));
//      error_log(filter_input(INPUT_POST, "source_lang"));
//      error_log(filter_input(INPUT_POST, "target_lang"));
      $corpus_dto = new corpus_dto();
      $corpus_dto->name = $_FILES['file']['name'];
      $corpus_dto->source_lang = filter_input(INPUT_POST, "source_lang");
      $corpus_dto->target_lang = filter_input(INPUT_POST, "target_lang");
      $corpus_dto->mode = filter_input(INPUT_POST, "mode");

      $corpus_dao = new corpus_dao();

      $handle = @fopen($tempFile, "r"); //read line one by one
      $values = array();

      $sentence_dao = new sentence_dao();
      $first_batch = true;
      
      if ($mode == "VAL") {
        try{
          while (!feof($handle)) // Loop 'til end of file.
          {
              $buffer = fgets($handle); // Read a line.
              $data = explode("\t", $buffer);

              $data = array_slice($data, 0, 2);

              if (!empty(trim($buffer)) && count($data) == 2 && strlen($data[0]) <= 5000 && strlen($data[1]) <= 5000) {
                $values[] = $data;// save values
              }
              else {
                error_log("WARNING : The following line of the file " . $_FILES['file']['name'] . " is not allowed : '" . $buffer . "'");
                continue;
              }

              // Save 1000 rows at the same time at most
              if (count($values) == 1000) {
                if ($first_batch){
                  $first_batch = false;
                  $corpus_dao->insertCorpus($corpus_dto);
                }            
                $result = $sentence_dao->insertBatchSentences($corpus_dto->id, $corpus_dto->source_lang, $corpus_dto->target_lang, $values);
                if ($result) {
                  $values = array();
                }
              }
          }
          if (count($values) > 0) {
            if ($first_batch) {
              $first_batch = false;
              $corpus_dao->insertCorpus($corpus_dto);
            }
          $result = $sentence_dao->insertBatchSentences($corpus_dto->id, $values);
          }
          if ($first_batch==false) {
            $corpus_dao->updateLinesInCorpus($corpus_dto->id);
          }
          else {
            if (!$corpus_dto->id >= 0){
              $corpus_dao->deleteCorpus($corpus_dto->id);
            }
            throw new CorpusException("Invalid format of corpus uploaded.");
          }
        }
        catch (Exception $ex){
          if (!$corpus_dto->id >= 0) {
          $corpus_dao->deleteCorpus($corpus_dto->id);
        }
        throw new CorpusException("Invalid format of corpus uploaded.");
      }
    } else if ($mode == "ADE") {
      // We compute the Quality Control sentences according to Cambridge Core paper and then we save
      // https://www.cambridge.org/core/journals/natural-language-engineering/article/can-machine-translation-systems-be-evaluated-by-the-crowd-alone/E29DA2BC8E6B99AA1481CC92FAB58462/core-reader


      try {
        $values = array();
        while (!feof($handle)) {
          $buffer = fgets($handle);
          $data = explode("\t", $buffer);
    
          $data = array_slice($data, 0, 2);
    
          if (!empty(trim($buffer)) && count($data) == 2 && strlen($data[0]) <= 5000 && strlen($data[1]) <= 5000) {
            $values[] = $data;
          } else {
            error_log("WARNING : The following line of the file " . $_FILES['file']['name'] . " is not allowed : '" . $buffer . "'");
            continue;
          }
        }

        $total = count($values);
        
        // Reference sentences
        $ref_group = array();
        $ref_total = floor($total * 0.1);
        $ref_group_total = floor($total / 3) + $ref_total;
        $ref_gpos = array(); for ($i = 0; $i < $ref_group_total; $i++) $ref_gpos[] = $i;
        $legit_in_ref = $ref_group_total - $ref_total;

        for ($i = 0; $i < $legit_in_ref; $i++) {
          $r = mt_rand(0, count($ref_gpos) - 1);
          $pos = $ref_gpos[$r];
          array_splice($ref_gpos, $r, 1);

          $ref_group[$pos] = array($values[0], 'legit');
          array_splice($values, 0, 1);
        }

        for ($i = 0; $i < $ref_total; $i++) {
          $r = mt_rand(0, count($ref_gpos) - 1);
          $pos = $ref_gpos[$r];
          array_splice($ref_gpos, $r, 1);

          $sentence = $values[0];
          $sentence[1] = $sentence[0];
          $ref_group[$pos] = array($sentence, 'ref');
        }

        // bad_reference sentences
        $bad_ref_group = array();
        $bad_ref_total = floor($total * 0.1);
        $bad_ref_group_total = floor($total / 3) + $bad_ref_total;
        $bad_ref_gpos = array(); for ($i = 0; $i < $bad_ref_group_total; $i++) $bad_ref_gpos[] = $i;
        $legit_in_bad_ref = $bad_ref_group_total - $bad_ref_total;

        for ($i = 0; $i < $legit_in_bad_ref; $i++) {
          $r = mt_rand(0, count($bad_ref_gpos) - 1);
          $pos = $bad_ref_gpos[$r];
          array_splice($bad_ref_gpos, $r, 1);

          $bad_ref_group[$pos] = array($values[0], 'legit');
          array_splice($values, 0, 1);
        }

        for ($i = 0; $i < $bad_ref_total; $i++) {
          $r = mt_rand(0, count($bad_ref_gpos) - 1);
          $pos = $bad_ref_gpos[$r];
          array_splice($bad_ref_gpos, $r, 1);

          $sentence_pair = $values[0];
          $sentence = $sentence_pair[1];
          $words = explode(" ", $sentence);
          $c = count($words);
          $remove = ($c < 4) ? 1 : ($c < 6) ? 2 : ($c < 9) ? 3 : ($c < 16) ? 5 : floor($c / 5);
          for ($j = 0; $j < $remove; $j++) {
             $p = mt_rand(0, $c); 
             array_splice($words, $p, 1); 
             $c = count($words);
          }

          $sentence_pair[1] = implode(" ", $words);
          $bad_ref_group[$pos] = array($sentence_pair, 'bad_ref');
        }

        // repeated sentences
        $repeated_group = array();
        $repeated_total = floor($total * 0.1);
        $repeated_group_total = floor($total / 3) + $repeated_total + ceil($total % 3);
        $repeated_gpos = array(); for ($i = 0; $i < $repeated_group_total; $i++) $repeated_gpos[] = $i;
        $legit_in_repeated = $repeated_group_total - $repeated_total;

        $added = array();

        for ($i = 0; $i < $legit_in_repeated; $i++) {
          $r = mt_rand(0, count($repeated_gpos) - 1);
          $pos = $repeated_gpos[$r];
          array_splice($repeated_gpos, $r, 1);

          $repeated_group[$pos] = array($values[0], 'legit');
          $added[] = $values[0];
          array_splice($values, 0, 1);
        }

        for ($i = 0; $i < $repeated_total; $i++) {
          $r = mt_rand(0, count($repeated_gpos) - 1);
          $pos = $repeated_gpos[$r];
          array_splice($repeated_gpos, $r, 1);

          $pos_added = mt_rand(0, count($added) - 1);
          $repeated_group[$pos] = array($added[$pos_added], 'rep');
          array_splice($added, $pos_added, 1);
        }

        // We are ready to save
        $sentences = array_merge($ref_group, array_merge($bad_ref_group, $repeated_group));
        $corpus_dao->insertCorpus($corpus_dto);
        $result = $sentence_dao->insertBatchSentences($corpus_dto->id, $corpus_dto->source_lang, $corpus_dto->target_lang, $sentences, $mode);
        if ($result) {
          $corpus_dao->updateLinesInCorpus($corpus_dto->id);
        }

      }  catch (Exception $ex) { 
        throw $ex;
      }

      fclose($handle);
      //header("HTTP/1.1 400 Bad Request");
      //echo "Ups error message";
    }
  }
} catch (CorpusException $ex){
  error_log($ex->getMessage());
  header("HTTP/1.1 500 Server error");
  echo "Oops! The corpus you tried to upload is invalid.";  
} catch (Exception $ex) {
  error_log($ex->getMessage());
  header("HTTP/1.1 500 Server error");
  echo "Oops! An error ocurred on server side, please contact with administrators.";
}