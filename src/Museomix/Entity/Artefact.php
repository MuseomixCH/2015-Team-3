<?php

namespace Museomix\Entity;

class Artefact
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $hashtag;

    /**
     * @var string
     */
    protected $color;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var string
     */
    protected $image;

    /**
     * @var \StdClass[]
     */
    protected $tweets = array();

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getHashtag()
    {
        return $this->hashtag;
    }

    /**
     * @param string $hashtag
     */
    public function setHashtag($hashtag)
    {
        $this->hashtag = $hashtag;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param mixed $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return \StdClass[]
     */
    public function getTweets()
    {
        return $this->tweets;
    }

    /**
     * @param \StdClass $tweet
     */
    public function addTweet(\StdClass $tweet)
    {
        $this->tweets[] = $tweet;
    }
}
