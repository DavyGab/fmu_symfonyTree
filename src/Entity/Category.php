<?php
namespace App\Entity;

//use Doctrine\Common\Persistence\Event\PreUpdateEventArgs;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="categories")
 * use repository for handy tree functions
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Category
{

    CONST SEPARATOR = ' / ';
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @ORM\Column(name="path", type="string", length=256, nullable=true)
     */
    private $path;

    /**
     * @ORM\Column(name="description", type="string", length=256)
     */
    private $description;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     */
    private $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function updatePath (LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $changedFields = $uow->getEntityChangeSet($this);

        if (isset($changedFields['parent']) || isset($changedFields['name'])) {
            $oldPath = $this->path;

            if (isset($changedFields['parent'])) {
                $rootPath = null === $this->parent ? '' : $this->parent->getPath();
            } else {
                $rootPath = explode(self::SEPARATOR, $oldPath);
                array_pop($rootPath);
                $rootPath = implode(self::SEPARATOR, $rootPath);
            }

            $this->path = $rootPath . self::SEPARATOR . $this->name;

			$meta = $em->getClassMetadata(get_class($this));
			$uow->recomputeSingleEntityChangeSet($meta, $this);
            $repository = $em->getRepository( get_class($this) );
            $repository->updateAllChildrenPath($oldPath, $this->path, $this->lft, $this->rgt);
        } elseif (empty($changedFields)) {
            $rootPath = null === $this->parent ? '' : $this->parent->getPath();
            $this->path = $rootPath . self::SEPARATOR . $this->name;
        }
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setParent(Category $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function addChild(Category $child = null)
    {
        $this->children->add($child);
        $child->setParent($this);
    }

    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @return mixed
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @return mixed
     */
    public function getLft()
    {
        return $this->lft;
    }

    public function __toString()
    {
        return $this->name;
    }
}