<?php

const DATABASE_URL = "databases\RC_2007-10_7816";

class Application
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "b4n4nfika", "reddit_comments");
        if (!$this->conn->set_charset("utf8")) {
            printf("Error loading character set utf8: %s\n", $this->conn->error);
            exit();
        } else {
            printf("Current character set: %s\n", $this->conn->character_set_name());
        }
        // if (!$this->conn->query("SET GLOBAL innodb_flush_log_at_trx_commit = 0")) {
        //     printf($this->conn->error);
        //     exit();
        // } else {
        //     printf("flush log stuff OK");
        // }
    }

    private function readFileContents()
    {
        $dataLine = '';
        $handle = fopen(DATABASE_URL, "r");

        if ($handle) {

            $commentArray = array();
            $index = 0;

            while (($dataLine = fgets($handle)) !== false) {

                $commentArrayObj = json_decode($dataLine, true, 512);
                array_push($commentArray, $commentArrayObj);

                $index++;

                // after 8500 lines (or at end of file), write to DB 
                if ($index > 8500) {
                    $this->writeToDB($commentArray);
                    $index = 0;
                }
            }

            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);

            $this->writeToDB($commentArray);
        }
    }

    private function writeToDB($commentArray)
    {
        // var_dump($commentArray);
        // get the comment keys
        // $id = ['id'];
        // $parent_id = ['parent_id'];
        // $link_id  = ['link_id'];
        // $name = ['name'];
        // $author = ['author'];
        // $body = ['body'];
        // $subreddit_id = ['subreddit_id'];
        // $subreddit = ['subreddit'];
        // $score = ['score'];
        // $created_utc = ['created_utc'];

        // $tableData = "comments (`id`, `parent_id`, `link_id`, `name`, `author`, `body`, `subreddit_id`, `subreddit`, `score`, `created_utc`)";
        // $valueData = "('$id', '$parent_id', '$link_id', '$name', '$author', '$body', '$subreddit_id', '$subreddit', '$score', '$created_utc')";

        // insert into mysql table
        // $sql = "INSERT IGNORE INTO comments 
        //         (`id`, `parent_id`, `link_id`, `name`, `author`, `body`, `subreddit_id`, `subreddit`, `score`, `created_utc`) 
        //         VALUES
        //         ('$id', '$parent_id', '$link_id', '$name', '$author', '$body', '$subreddit_id', '$subreddit', '$score', '$created_utc')";

        // var_dump($commentArray);


        foreach ($commentArray as $commentData) {
            $this->conn->autocommit(FALSE);
            // $this->conn->query("START TRANSACTION");

            $id = $commentData['id'];
            $parent_id = $commentData['parent_id'];
            $link_id  = $commentData['link_id'];
            $name = $commentData['name'];
            $author = $commentData['author'];
            $body = $commentData['body'];
            $subreddit_id = $commentData['subreddit_id'];
            $subreddit = $commentData['subreddit'];
            $score = $commentData['score'];
            $created_utc = $commentData['created_utc'];

            // $columns = implode(", ", array_keys($commentData));
            $escaped_values = array_map(array($this->conn, 'real_escape_string'), array_values($commentData));
            $values  = implode("', '", $escaped_values);

            $query = "INSERT IGNORE INTO 
            comments (`id`, `parent_id`, `link_id`, `name`, `author`, `body`, `subreddit_id`, `subreddit`, `score`, `created_utc`) 
            VALUES ('$id', '$parent_id', '$link_id', '$name', '$author', '$body', '$subreddit_id', '$subreddit', '$score', '$created_utc')";

            // $query = "INSERT IGNORE INTO 
            // comments (`id`, `parent_id`, `link_id`, `name`, `author`, `body`, `subreddit_id`, `subreddit`, `score`, `created_utc`)
            // VALUES (?,?,?,?,?,?,?,?,?,?)";

            if ($stmt = $this->conn->prepare($query)) {

                // $stmt->bind_param("ssssssssis", $id, $parent_id, $link_id, $name, $author, $body, $subreddit_id, $subreddit, $score, $created_utc);


                $stmt->execute();

                $stmt->close();
            }
            $this->conn->query("COMMIT");


            // if (!mysqli_query($this->conn, $query)) {
            //     die('Error : ' . mysqli_error($this->conn));
            // }
        }
    }

    public function run()
    {
        $this->readFileContents();
    }
}

// Needed keys

// id 
// parent_id
// link_id 
// name
// author
// body
// subreddit_id
// subreddit
// score
// created_utc
