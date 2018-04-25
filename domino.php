<?php
$domino = new Domino();
$domino->prepareGame();
$domino->playGame();

class Domino
{

    private $cards          = [];

    private $PlayerOne      = [];

    private $PlayerTwo      = [];

    private $gameActions    = [];

    private $bottomPile     = [];

    private $bottomPileEnds = [];

    private $players        = ['PlayerOne', 'PlayerTwo'];

    private $currentPlayer;

    private $end            = false;

    /**
     * We first need the 28 pieces for the game and shuffle 7 to each player
     */
    public function prepareGame()
    {
        $this->createCards();
        $this->shuffleCards();
    }

    /**
     * Create the 28 pieces of the game and mixed them (arrange random)
     */
    private function createCards()
    {
        for ($i = 0; $i <= 6; $i++) {
            for ($j = $i; $j <= 6; $j++) {
                $this->cards[] = [$i, $j];
            }
        }

        uksort($this->cards, function () { return rand() > rand(); });
    }

    /**
     * Shuffle each player 7 cards/pieces
     */
    private function shuffleCards()
    {
        $this->PlayerOne = array_splice($this->cards, 0, 7);
        $this->PlayerTwo = array_splice($this->cards, 0, 7);
    }

    /**
     * This is where the action happens
     */
    public function playGame()
    {
        $this->firstMove();

        // play until one of the players has no cards in his hand and it's declared the winner
        //while ($this->end == false) {
        $this->playRound();
        //}

        var_dump($this->gameActions);

    }

    /**
     * First move is different
     * We select one random player to start the game
     * We select a piece from the remaining pieces to be the first one to start the game
     */
    private function firstMove()
    {
        $players = $this->players;
        shuffle($players);
        $current              = current($players);
        $this->currentPlayer  = $current;
        $firstCard            = current(array_splice($this->cards, 0, 1));
        $this->gameActions[]  = 'The first card is ' . $this->getCardName($firstCard);
        $this->gameActions[]  = $current . ' is the first one to play';
        $this->bottomPileEnds = $firstCard;
        $this->bottomPile[]   = $firstCard;
    }

    /**
     * Transforms the card array into a 'readable' domino piece
     *
     * @param array $values
     *
     * @return string
     */
    private function getCardName(array $values)
    {
        return '<' . current($values) . ':' . end($values) . '>';
    }

    /**
     * For the current player checks to see if one of the pieces from his hand can be used in the bottom pile
     * If no, the user must draw another piece until it has one piece that is can be used
     * After using a piece - if it is the last piece he will win the game, if not - his turn ends and the other player can play his turn
     */
    private function playRound()
    {
        $currentPlayer  = $this->currentPlayer;
        $bottomPileEnds = $this->bottomPileEnds;
        $endTurn        = true;
        $cards          = $this->$currentPlayer;

        foreach ($cards as $key => $value) {
            $endTurn = $this->checkCard($value, $bottomPileEnds, $currentPlayer, $key);
            if ($endTurn) {
                break;
            }
        }

        if (!$endTurn && !empty($this->cards)) {
            // draw a card and 'put it in hand'. Same player can continue the game
            $draw                 = array_splice($this->cards, 0, 1);
            $this->gameActions[]  = $currentPlayer . ' draws the card ' . $this->getCardName($draw[0]);
            $this->$currentPlayer = array_merge($this->$currentPlayer, $draw);
        } else {
            // Pass the round to the other player
            $players = array_flip($this->players);
            unset($players[$currentPlayer]);
            $currentPlayer = key($players);
        }

        // If current player has his hand empty -> he is the winner
        if (!empty($this->$currentPlayer)) {
            $this->currentPlayer = $currentPlayer;
            $this->playRound();
        } else {
            $this->end           = true;
            $this->gameActions[] = "Game Over. Player " . $this->currentPlayer . " won the game";
        }
    }

    /**
     * Checks a card against the bottom pile and add it if it can be played
     *
     * @param $value
     * @param $bottomPileEnds
     * @param $currentPlayer
     * @param $key
     *
     * @return bool
     */
    private function checkCard($value, $bottomPileEnds, $currentPlayer, $key): bool
    {
        if ($value[1] == $bottomPileEnds[0]) {
            // Card will go on the left of the pile
            $this->bottomPile     = array_merge([$value], $this->bottomPile);
            $this->bottomPileEnds = [$value[0], $bottomPileEnds[1]];
            $this->playCard($value, $currentPlayer, $key, 'left');
            $endTurn = true;
        } elseif ($value[1] == $bottomPileEnds[1]) {
            // Card will go on the right at the pile but flipped
            $this->bottomPile     = array_merge($this->bottomPile, [array_reverse($value)]);
            $this->bottomPileEnds = [$bottomPileEnds[0], $value[0]];
            $this->playCard($value, $currentPlayer, $key, 'right');
            $endTurn = true;
        } elseif ($value[0] == $bottomPileEnds[0]) {
            // Card will go at the left at the pile but flipped
            $this->bottomPile     = array_merge([array_reverse($value)], $this->bottomPile);
            $this->bottomPileEnds = [$value[1], $bottomPileEnds[1]];
            $this->playCard($value, $currentPlayer, $key, 'left');
            $endTurn = true;
        } elseif ($value[0] == $bottomPileEnds[1]) {
            // Card will go at the right of the pie
            $this->bottomPile     = array_merge($this->bottomPile, [$value]);
            $this->bottomPileEnds = [$bottomPileEnds[0], $value[1]];
            $this->playCard($value, $currentPlayer, $key, 'right');
            $endTurn = true;
        } else {
            $endTurn = false;
        }

        return $endTurn;
    }

    /**
     * Play the card and remove it from 'hand'
     *
     * @param $value
     * @param $currentPlayer
     * @param $key
     * @param $position
     */
    private function playCard($value, $currentPlayer, $key, $position)
    {
        $this->gameActions[] = $currentPlayer . ' plays the card ' . $this->getCardName($value) . ' by adding it to the ' . $position . ' of the pile';

        $currentBoard = "";
        foreach ($this->bottomPile as $k => $item) {
            $currentBoard .= $this->getCardName($item);
        }
        $this->gameActions[] = 'The current board is: ' . $currentBoard;
        unset($this->$currentPlayer[$key]);
    }
}