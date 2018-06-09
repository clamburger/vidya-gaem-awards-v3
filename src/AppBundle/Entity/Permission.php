<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Permission
 */
class Permission
{
    const STANDARD_PERMISSIONS = [
        'add_user' => 'Add a new user to the team',
        'audit_log_view' => 'View the website\'s audit log',
        'edit_config' => 'Edit site config',
        'LEVEL_4' => 'Gives access to everything except for critical areas',
        'LEVEL_5' => 'Gives complete admin access',
        'profile_edit_details' => 'Edit user details',
        'profile_edit_groups' => 'Edit user groups',
        'profile_edit_notes' => 'Edit notes attached to user profile',
        'profile_view' => 'View user profiles',
        'referrers_view' => 'View where site visitors are coming from',
        'sentiment_edit' => 'Edit the sentiment analysis rules',
        'twitch_monitor' => 'View the Twitch chat status page',
        'view_debug_output' => 'Show detailed error messages when something goes wrong',
        'view_unfinished_pages' => 'View some pages before they are ready for the public',
    ];

    const STANDARD_PERMISSION_INHERITANCE = [
        'LEVEL_4' => [
            'view_unfinished_pages', 'profile_view', 'profile_edit_notes', 'add_user', 'audit_log_view',
            'profile_edit_details', 'referrers_view', 'sentiment_edit', 'twitch_monitor'
        ],
        'LEVEL_5' => [
            'LEVEL_4', 'edit_config', 'profile_edit_groups'
        ]
    ];

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $description;

    /**
     * @var ArrayCollection|Permission[]
     */
    private $children;

    /**
     * @var ArrayCollection|Permission[]
     */
    private $parents;

    /**
     * @var ArrayCollection|User[]
     */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->parents = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param string $id
     *
     * @return Permission
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Permission
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add child
     *
     * @param Permission $child
     *
     * @return Permission
     */
    public function addChild(Permission $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param Permission $child
     */
    public function removeChild(Permission $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * To avoid unnecessary database calls, we assume a permission can only have children if it's a LEVEL permission.
     * @return ArrayCollection|Permission[]
     */
    public function getChildren()
    {
        if (substr($this->getId(), 0, 5) !== 'LEVEL') {
            return new ArrayCollection();
        }
        return $this->children;
    }

    /**
     * @return ArrayCollection|Permission[]
     */
    public function getChildrenRecurvise()
    {
        $permissions = new ArrayCollection();

        foreach ($this->getChildren() as $child) {
            foreach ($child->getChildrenRecurvise() as $grandchild) {
                $permissions->add($grandchild);
            }
            $permissions->add($child);
        }

        return $permissions;
    }

    /**
     * Add user
     *
     * @param User $user
     *
     * @return Permission
     */
    public function addUser(User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return ArrayCollection|Permission[]
     */
    public function getParents()
    {
        return $this->parents;
    }

    public function __toString()
    {
        return $this->getId();
    }
}

