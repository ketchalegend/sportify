<?php

namespace Devlabs\App;

/**
 * Class Prediction
 * @package Devlabs\App
 */
class Prediction
{
    use MatchCommon;

    public $id = null;
    public $matchId = null;
    public $userId = null;
    public $homeGoals = null;
    public $awayGoals = null;
    public $points = null;
    public $scoreAdded = null;

    public function __construct($id = null, $matchId = null, $userId = null,
                                $homeGoals = null, $awayGoals = null, $points = null, $scoreAdded = null)
    {
        $this->id = $id;
        $this->matchId = $matchId;
        $this->userId = $userId;
        $this->homeGoals = $homeGoals;
        $this->awayGoals = $awayGoals;
        $this->points = $points;
        $this->scoreAdded = $scoreAdded;
    }

    /**
     * Load a prediction for a given user's match by passing in User and Match objects
     *
     * @param User $user
     * @param Match $match
     */
    public function loadByUserAndMatch(User $user, Match $match)
    {
        $query = $GLOBALS['db']->query(
            "SELECT * FROM predictions WHERE match_id = :match_id AND user_id = :user_id",
            array('match_id' => $match->id, 'user_id' => $user->id)
        );

        if ($query) {
            $this->id = $query[0]['id'];
            $this->matchId = $query[0]['match_id'];
            $this->userId = $query[0]['user_id'];
            $this->homeGoals = $query[0]['home_goals'];
            $this->awayGoals = $query[0]['away_goals'];
            $this->points = $query[0]['points'];
            $this->scoreAdded = $query[0]['score_added'];
        }
    }

    /**
     * Method for making a prediction
     * by inserting or updating prediction data in the database
     *
     * @param User $user
     * @param Match $match
     * @param $homeGoals
     * @param $awayGoals
     */
    public function makePrediction(User $user, Match $match, $homeGoals, $awayGoals)
    {
        // convert string numbers to integer data
        $homeGoals = (int) $homeGoals;
        $awayGoals = (int) $awayGoals;

        $this->loadByUserAndMatch($user, $match);

        // check if any prediction has been loaded from the database
        if ($this->id == null) {
            $this->insertPrediction($user, $match, $homeGoals, $awayGoals);
        } else {
            $this->updatePrediction($user, $match, $homeGoals, $awayGoals);
        }
    }

    /**
     * Method for updating a prediction in the database
     *
     * @param User $user
     * @param Match $match
     * @param $homeGoals
     * @param $awayGoals
     * @return mixed
     */
    public function updatePrediction(User $user, Match $match, $homeGoals, $awayGoals)
    {
        return $GLOBALS['db']->query(
            "UPDATE predictions
            SET home_goals = :home_goals , away_goals = :away_goals
            WHERE match_id = :match_id AND user_id = :user_id",
            array(
                'user_id' => $user->id,
                'match_id' => $match->id,
                'home_goals' => $homeGoals,
                'away_goals' => $awayGoals
            )
        );
    }

    /**
     * Method for inserting a new prediction in the database
     *
     * @param User $user
     * @param Match $match
     * @param $homeGoals
     * @param $awayGoals
     * @return mixed
     */
    public function insertPrediction(User $user, Match $match, $homeGoals, $awayGoals)
    {
        return $GLOBALS['db']->query(
            "INSERT IGNORE INTO predictions(match_id,user_id,home_goals,away_goals)
            VALUES(:match_id, :user_id, :home_goals, :away_goals)",
            array(
                'user_id' => $user->id,
                'match_id' => $match->id,
                'home_goals' => $homeGoals,
                'away_goals' => $awayGoals
            )
        );
    }

    /**
     * Calculate the points from the prediction
     *
     * @param Match $match
     * @return int
     */
    public function calculatePoints(Match $match)
    {
        if (($this->homeGoals === $match->homeGoals) && ($this->awayGoals === $match->awayGoals)) {
            return POINTS_EXACT;
        } else if ($this->getResultOutcome() === $match->getResultOutcome()) {
            return POINTS_OUTCOME;
        }

        return 0;
    }

    /**
     * Method for setting the points gained for a prediction
     *
     * @param $points
     * @return mixed
     */
    public function setPoints($points)
    {
        return $GLOBALS['db']->query(
            "UPDATE predictions
            SET points = :points , score_added = 1
            WHERE id = :id",
            array(
                'id' => $this->id,
                'points' => $points
            )
        );
    }

    /**
     * Method for checking if the prediction data is valid and suitable for inserting or updating in the database
     *
     * @param Match $match
     * @param $status_message
     * @return bool
     */
    public static function validateData(Match $match, $homeGoals, $awayGoals, &$status_message) {
        $isDataInvalid = (($homeGoals === "" || $homeGoals === null) ||
            ($awayGoals === "" || $awayGoals === null));

        if ($isDataInvalid) {
            $status_message = 'Please provide values for both home and away goals.';

            return false;
        } else if (!(is_numeric($homeGoals) && $homeGoals >= 0) || !(is_numeric($awayGoals) && $awayGoals >= 0)) {
            $status_message = 'Both home and away goals should be non-negative integers.';

            return false;
        } else if ($match->hasStarted()) {
            $status_message = 'Match has already started, cannot make or edit prediction.';

            return false;
        } else {
            $status_message = 'OK, valid bet.';
        }

        return true;
    }
}