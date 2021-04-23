<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="sku")
 */
class Sku implements \JsonSerializable {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    private $name;
    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param mixed $id
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
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            "name" => $this->getName(),
            "quantity" => $this->getQuantity()
        ];
    }
}