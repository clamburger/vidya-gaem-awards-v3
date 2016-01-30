<?php

namespace VGA\Model;

/**
 * Nominee
 */
class Nominee
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $shortName;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $subtitle;

    /**
     * @var string
     */
    private $image;

    /**
     * @var string
     */
    private $flavorText;

    /**
     * @var \VGA\Model\Category
     */
    private $category;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set shortName
     *
     * @param string $shortName
     *
     * @return Nominee
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * Get shortName
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param string $name
     * @return Nominee
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set subtitle
     *
     * @param string $subtitle
     *
     * @return Nominee
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Get subtitle
     *
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return Nominee
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set flavorText
     *
     * @param string $flavorText
     *
     * @return Nominee
     */
    public function setFlavorText($flavorText)
    {
        $this->flavorText = $flavorText;

        return $this;
    }

    /**
     * Get flavorText
     *
     * @return string
     */
    public function getFlavorText()
    {
        return $this->flavorText;
    }

    /**
     * Set category
     *
     * @param \VGA\Model\Category $category
     *
     * @return Nominee
     */
    public function setCategory(\VGA\Model\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \VGA\Model\Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}

