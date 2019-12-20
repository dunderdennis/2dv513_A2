<?php

const DATABASE_URL = "databases\RC_2007-10";

class Application
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "b4n4nfika", "reddit_comments");
    }

    private function readFileContents(): string
    {
        $redditJSONData = '{ "comments": [';

        $handle = fopen(DATABASE_URL, "r");
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                $redditJSONData .= $buffer . ',';
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        } else {
            echo '$handle = false';
        }

        // remove the last ','
        $redditJSONData = rtrim($redditJSONData, ',');

        $redditJSONData .= ']}';

        // echo $redditJSONData;

        return $redditJSONData;
    }

    private function writeToDB($jsonData)
    {
        //convert json object to php associative array
        try {
            $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            echo $exception->getMessage(); // displays "Syntax error"  
        }

        // var_dump($data);
        $comments = $data['comments'];
        // var_dump($comments[1]);

        foreach ($comments as $comment) {
            // get the comment details
            $id = mysqli_real_escape_string($this->conn, $comment['id']);
            $parent_id = mysqli_real_escape_string($this->conn, $comment['parent_id']);
            $link_id  = mysqli_real_escape_string($this->conn, $comment['link_id']);
            $name = mysqli_real_escape_string($this->conn, $comment['name']);
            $author = mysqli_real_escape_string($this->conn, $comment['author']);
            $body = mysqli_real_escape_string($this->conn, $comment['body']);
            $subreddit_id = mysqli_real_escape_string($this->conn, $comment['subreddit_id']);
            $subreddit = mysqli_real_escape_string($this->conn, $comment['subreddit']);
            $score = mysqli_real_escape_string($this->conn, $comment['score']);
            $created_utc = mysqli_real_escape_string($this->conn, $comment['created_utc']);

            // $id = $comment['id'];
            // $parent_id = $comment['parent_id'];
            // $link_id  = $comment['link_id'];
            // $name = $comment['name'];
            // $author = $comment['author'];
            // $body = $comment['body'];
            // $subreddit_id = $comment['subreddit_id'];
            // $subreddit = $comment['subreddit'];
            // $score = $comment['score'];
            // $created_utc = $comment['created_utc'];
            // var_dump($comment);

            // $comment['id'] = $commentData['id'];
            // $comment['parent_id'] = $commentData['parent_id'];
            // $comment['link_id']  = $commentData['link_id'];
            // $comment['name'] = $commentData['name'];
            // $comment['author'] = $commentData['author'];
            // $comment['body'] = $commentData['body'];
            // $comment['subreddit_id'] = $commentData['subreddit_id'];
            // $comment['subreddit'] = $commentData['subreddit'];
            // $comment['score'] = $commentData['score'];
            // $comment['created_utc'] = $commentData['created_utc'];

            //insert into mysql table

            $sql = "INSERT INTO comments 
            (`id`, `parent_id`, `link_id`, `name`, `author`, `body`, `subreddit_id`, `subreddit`, `score`, `created_utc`) 
            VALUES
            ('$id', '$parent_id', '$link_id', '$name', '$author', '$body', '$subreddit_id', '$subreddit', '$score', '$created_utc')";

            // var_dump($sql);

            if (!mysqli_query($this->conn, $sql)) {
                die('Error : ' . mysqli_error($this->conn));
            }
        }
    }
    public function run()
    {
        $redditJSONData = $this->readFileContents();
        $this->writeToDB($redditJSONData);
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
