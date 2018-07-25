<?php
/**
 * Created by PhpStorm.
 * User: xubuntu
 * Date: 25.07.18
 * Time: 16:04
 */

namespace SingAppBundle\Twig;


use SingAppBundle\Entity\PinterestAccount;
use SingAppBundle\Services\PinterestService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GetBoardPinterest extends \Twig_Extension
{
    /**
     * @var PinterestService $pinterestService
     */
    private $pinterestService;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getBoardSelect', array($this, 'getBoardSelect'))
        );
    }

    public function __construct(ContainerInterface $container)
    {
        $this->pinterestService = $container->get('app.pinterest.service');
    }

    public function getBoardSelect($pinterestAccount)
    {
        $select= null;
        if($pinterestAccount instanceof PinterestAccount) {
            try {
                $boards = $this->pinterestService->getBoards($pinterestAccount);
                $select = '<select id="board"  data-style="btn-primary"
                                    data-none-selected-text="Select social networks" name="board" required>';
                foreach ($boards as $board) {
                    $select .= '<option value="' . $board['data']['name'] . '">' . $board['data']['name'] . '</option>';
                }
                $select .= '</select>';
            } catch (\Exception $e) {
                $select = '<span class="error-api"></span>';
            }
        }

        return $select;
    }
}