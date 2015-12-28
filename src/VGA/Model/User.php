<?php

namespace VGA\Model;

/**
 * User
 */
class User
{
    /**
     * @var string
     */
    private $steamID;

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $special;

    /**
     * @var \DateTime
     */
    private $firstLogin;

    /**
     * @var \DateTime
     */
    private $lastLogin;

    /**
     * @var string
     */
    private $primaryRole;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $notes;

    /**
     * @var string
     */
    private $website;

    /**
     * @var string
     */
    private $avatar;

    /**
     * @var \VGA\Model\LoginToken
     */
    private $loginToken;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $votes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $permissions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->votes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set steamID
     *
     * @param string $steamID
     *
     * @return User
     */
    public function setSteamID($steamID)
    {
        $this->steamID = $steamID;

        return $this;
    }

    /**
     * Get steamID
     *
     * @return string
     */
    public function getSteamID()
    {
        return $this->steamID;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set special
     *
     * @param boolean $special
     *
     * @return User
     */
    public function setSpecial($special)
    {
        $this->special = $special;

        return $this;
    }

    /**
     * Get special
     *
     * @return boolean
     */
    public function getSpecial()
    {
        return $this->special;
    }

    /**
     * Set firstLogin
     *
     * @param \DateTime $firstLogin
     *
     * @return User
     */
    public function setFirstLogin($firstLogin)
    {
        $this->firstLogin = $firstLogin;

        return $this;
    }

    /**
     * Get firstLogin
     *
     * @return \DateTime
     */
    public function getFirstLogin()
    {
        return $this->firstLogin;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     *
     * @return User
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set primaryRole
     *
     * @param string $primaryRole
     *
     * @return User
     */
    public function setPrimaryRole($primaryRole)
    {
        $this->primaryRole = $primaryRole;

        return $this;
    }

    /**
     * Get primaryRole
     *
     * @return string
     */
    public function getPrimaryRole()
    {
        return $this->primaryRole;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return User
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return User
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     *
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set loginToken
     *
     * @param \VGA\Model\LoginToken $loginToken
     *
     * @return User
     */
    public function setLoginToken(\VGA\Model\LoginToken $loginToken = null)
    {
        $this->loginToken = $loginToken;

        return $this;
    }

    /**
     * Get loginToken
     *
     * @return \VGA\Model\LoginToken
     */
    public function getLoginToken()
    {
        return $this->loginToken;
    }

    /**
     * Add vote
     *
     * @param \VGA\Model\Vote $vote
     *
     * @return User
     */
    public function addVote(\VGA\Model\Vote $vote)
    {
        $this->votes[] = $vote;

        return $this;
    }

    /**
     * Remove vote
     *
     * @param \VGA\Model\Vote $vote
     */
    public function removeVote(\VGA\Model\Vote $vote)
    {
        $this->votes->removeElement($vote);
    }

    /**
     * Get votes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Add permission
     *
     * @param \VGA\Model\Permission $permission
     *
     * @return User
     */
    public function addPermission(\VGA\Model\Permission $permission)
    {
        $this->permissions[] = $permission;

        return $this;
    }

    /**
     * Remove permission
     *
     * @param \VGA\Model\Permission $permission
     */
    public function removePermission(\VGA\Model\Permission $permission)
    {
        $this->permissions->removeElement($permission);
    }

    /**
     * Get permissions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
