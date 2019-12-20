<?php

const DATABASE_URL = "databases\RC_2007-10_7816";

class Application
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "", "2dv513_reddit_comments");
    }

    private function readFileContents()
    {
        $this->timeAtStart = microtime(true);


        $handle = fopen(DATABASE_URL, "r");

        if ($handle) {

            $dataLine = '';
            $index = 0;

            $commentQuery = "INSERT IGNORE INTO commentTable (`id`, `parent_id`, `link_id`, `name`, `author`, `body`, `subreddit_id`, `subreddit`, `score`, `created_utc`) VALUES ";
            $subredditQuery = "INSERT IGNORE INTO subredditTable (`id`, `subreddit_id`, `subreddit`) VALUES ";
            $authorQuery = "INSERT IGNORE INTO authorTable (`id`, `author`) VALUES ";

            while (($dataLine = fgets($handle)) !== false) {

                $commentData = json_decode($dataLine, true);

                $id = $commentData['id'];
                $parent_id = $commentData['parent_id'];
                $link_id  = $commentData['link_id'];
                $name = $commentData['name'];
                $author = $commentData['author'];
                $body = mysqli_real_escape_string($this->conn, $commentData['body']);
                $subreddit_id = $commentData['subreddit_id'];
                $subreddit = $commentData['subreddit'];
                $score = $commentData['score'];
                $created_utc = $commentData['created_utc'];

                $commentQuery .= "('$id', '$parent_id', '$link_id', '$name', '$author', '$body', '$subreddit_id', '$subreddit', '$score', FROM_UNIXTIME('$created_utc')),";
                $subredditQuery .= "('$id', '$subreddit_id', '$subreddit'),";
                $authorQuery .= "('$id', '$author'),";

                $index++;

                // after 1000 lines (or at end of file), write to DB 
                if ($index > 1000) {
                    $this->writeToDB($commentQuery, $subredditQuery, $authorQuery);
                    $index = 0;
                }
            }

            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            } else {
                $this->writeToDB($commentQuery, $subredditQuery, $authorQuery);
            }

            fclose($handle);
        }

        $timeAtFinish = microtime(true);

        echo $timeAtFinish - $this->timeAtStart . ' seconds';
    }

    private function writeToDB($commentQuery, $subredditQuery, $authorQuery)
    {
        mysqli_query($this->conn, "START TRANSACTION");

        // if (!$this->conn->query(rtrim($commentQuery, ','))) {
        //     die("CANNOT EXECUTE" . $this->conn->error . "\n");
        // }
        $this->conn->query(rtrim($commentQuery, ','));

        $this->conn->query(rtrim($subredditQuery, ','));

        $this->conn->query(rtrim($authorQuery, ','));

        mysqli_query($this->conn, "COMMIT");
    }

    public function run()
    {
        $this->readFileContents();
    }
}
