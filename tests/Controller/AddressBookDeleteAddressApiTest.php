<?php

namespace Tests\Sylius\ShopApiPlugin\Controller;


use Lakion\ApiTestCase\JsonApiTestCase;
use PHPUnit\Framework\Assert;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Repository\AddressRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\Sylius\ShopApiPlugin\Controller\Utils\ShopUserLoginTrait;

class AddressBookDeleteAddressApiTest extends JsonApiTestCase
{
    use ShopUserLoginTrait;

    private static $acceptAndContentTypeHeader = ['CONTENT_TYPE' => 'application/json', 'ACCEPT' => 'application/json'];

    /**
     * @test
     */
    public function it_deletes_address_from_address_book()
    {
        $this->loadFixturesFromFile('customer.yml');
        $this->loadFixturesFromFile('country.yml');
        $this->loadFixturesFromFile('address.yml');
        $this->logInUser('oliver@queen.com', '123pa$$word');

        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = $this->get('sylius.repository.address');
        /** @var AddressInterface $address */
        $address = $addressRepository->findOneBy(['street' => 'Kupreska']);

        $this->client->request('DELETE', sprintf("/shop-api/address-book/%s", $address->getId()), [], [], self::$acceptAndContentTypeHeader);
        $response = $this->client->getResponse();

        $address = $addressRepository->findOneBy(['street' => 'Kupreska']);
        Assert::assertNull($address);

        $this->assertResponseCode($response, Response::HTTP_NO_CONTENT);
    }

    /**
     * @test
     */
    public function it_returns_bad_request_exception_if_address_has_not_been_found()
    {
        $this->loadFixturesFromFile('customer.yml');
        $this->loadFixturesFromFile('country.yml');
        $this->loadFixturesFromFile('address.yml');
        $this->logInUser('oliver@queen.com', '123pa$$word');

        $this->client->request('DELETE',sprintf("/shop-api/address-book/-1"), [], [], self::$acceptAndContentTypeHeader);
        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     */
    public function it_validates_if_current_user_is_owner_of_address()
    {
        $this->loadFixturesFromFile('customer.yml');
        $this->loadFixturesFromFile('country.yml');
        $this->loadFixturesFromFile('address.yml');
        $this->logInUser('oliver@queen.com', '123pa$$word');

        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = $this->get('sylius.repository.address');
        /** @var AddressInterface $address */
        $address = $addressRepository->findOneBy(['street' => 'Vukovarska']);

        $this->client->request('DELETE', sprintf("/shop-api/address-book/%s", $address->getId()), [], [], self::$acceptAndContentTypeHeader);
        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}