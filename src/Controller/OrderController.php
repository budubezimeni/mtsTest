<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\LockMode;
use \App\Entity\Sku;
use \App\Entity\Order;

/**
 * Class OrderController
 * @package App\Controller
 */
class OrderController extends AbstractController
{

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     * @throws \Exception
     * @Route("/api/orders", name="order_create", methods={"POST"})
     */
    public function createOrder(Request $request, EntityManagerInterface $entityManager){
        try{
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('cart_data')){
                throw new \Exception('Required parameter "cart_data" is missing!');
            }

            $cart_data = $request->get('cart_data');
            /*
             * Здесь я помещаю в транзакцию довольно много операций
             * без учета нагрузки и конкурентных запросов, поскольку реализуется
             * минимальный функционал, в рамках которого все эти операции должны
             * быть выполнены все вместе.
             * */
            $entityManager->beginTransaction();
            foreach($cart_data as $id => $quantity) {
                // Получаем id sku из запроса
                $entity = $entityManager->find(Sku::class, $id);
                if (!$entity){
                    // Откатываемся, если такого в базе нет
                    $entityManager->rollback();
                    throw new \Exception('No such sku (' . $id . ')');
                }
                // Ставим блокировку на изменение нужного sku, стобы проконтролировать количество при списании
                $entityManager->lock($entity, LockMode::PESSIMISTIC_WRITE);
                if ($entity->getQuantity() - $quantity < 0){
                    // Откатываемся, если нет нужного количества
                    $entityManager->rollback();
                    throw new \Exception('There is no enough sku (' . $id . ')');
                }
                // Списываем
                $entity->setQuantity($entity->getQuantity() - $quantity);
                $entityManager->flush();
            }
            $order = new Order();
            // Записываем данные корзины из запроса в таблицу заказов
            $order->setParams(json_encode($cart_data, true));
            $entityManager->persist($order);
            $entityManager->flush();

            $entityManager->commit();

            $data = [
                'status' => 200,
                'success' => "Order created successfully",
            ];
            return $this->response($data);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => $e->getMessage(),
            ];
            return $this->response($data, 422);
        }

    }

    /**
     * Returns a JSON response
     *
     * @param array $data
     * @param $status
     * @param array $headers
     * @return JsonResponse
     */
    public function response($data, $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    protected function transformJsonBody(\Symfony\Component\HttpFoundation\Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }
}
