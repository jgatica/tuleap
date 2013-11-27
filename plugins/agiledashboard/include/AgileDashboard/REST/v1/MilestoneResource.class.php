<?php
/**
 * Copyright (c) Enalean, 2013. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Tuleap\AgileDashboard\REST\v1;

use \Tuleap\REST\Header;
use \Luracast\Restler\RestException;
use \PlanningFactory;
use \Tracker_ArtifactFactory;
use \Tracker_FormElementFactory;
use \TrackerFactory;
use \Planning_MilestoneFactory;
use \Project_AccessProjectNotFoundException;
use \Project_AccessException;
use \UserManager;
use \URLVerification;
use \Planning_Milestone;

/**
 * Wrapper for milestone related REST methods
 */
class MilestoneResource {

    const MAX_LIMIT = 100;

    /** @var \Planning_MilestoneFactory */
    private $milestone_factory;

    public function __construct() {
        $this->milestone_factory = new Planning_MilestoneFactory(
            PlanningFactory::build(),
            Tracker_ArtifactFactory::instance(),
            Tracker_FormElementFactory::instance(),
            TrackerFactory::instance()
        );
    }

    /**
     * @url OPTIONS
     */
    public function options() {
        Header::allowOptions();
    }

    /**
     * Get milestone
     *
     * Get the definition of a given the milestone
     *
     * @url GET {id}
     *
     * @param int $id Id of the milestone
     *
     * @return MilestoneRepresentation
     *
     * @throws 403
     * @throws 404
     */
    protected function getId($id) {
        $user      = $this->getCurrentUser();
        $milestone = $this->getMilestoneById($user, $id);
        $this->sendAllowHeadersForMilestone($milestone);

        return new MilestoneRepresentation($milestone);
    }

    /**
     * Return info about milestone if exists
     *
     * @url OPTIONS {id}
     *
     * @param string $id Id of the milestone
     *
     * @throws 403
     * @throws 404
     */
    protected function optionsId($id) {
        $milestone = $this->getMilestoneById($this->getCurrentUser(), $id);
        $this->sendAllowHeadersForMilestone($milestone);
    }

    /**
     * @url OPTIONS {id}/milestones
     *
     * @param int $id ID of the milestone
     *
     * @throws 403
     * @throws 404
     */
    protected function optionsSubmilestones($id) {
        $this->getMilestoneById($this->getCurrentUser(), $id);
        $this->sendAllowHeaderForSubmilestones();
    }

    /**
     * Get sub-milestones
     *
     * Get the sub-milestones of a given milestone.
     * A sub-milestone is a decomposition of a milestone (for instance a Release has Sprints as submilestones)
     *
     * @url GET {id}/milestones
     *
     * @param int $id Id of the milestone
     *
     * @return array MilestoneRepresentation
     *
     * @throws 403
     * @throws 404
     */
    protected function getSubmilestones($id) {
        $user      = $this->getCurrentUser();
        $milestone = $this->getMilestoneById($user, $id);
        $this->sendAllowHeaderForSubmilestones();

        return array_map(
            function (Planning_Milestone $milestone) {
                return new MilestoneRepresentation($milestone);
            },
            $this->milestone_factory->getSubMilestones($user, $milestone)
        );
    }

    /**
     * Get backlog items
     *
     * Get the backlog items of a given milestone
     *
     * @url GET {id}/backlog_items
     *
     * @param int $id     Id of the planning
     * @param int $limit  Number of elements displayed per page
     * @param int $offset Position of the first element to display
     *
     * @return array MilestoneInfoRepresentation
     *
     * @throws 403
     * @throws 404
     */
    protected function getBacklogItems($id, $limit = 10, $offset = 0) {
        $this->checkContentLimit($limit);

        $milestone = $this->getMilestoneById($this->getCurrentUser(), $id);
        $this->sendAllowHeaderForBacklogItems();

        $backlog_items = $this->getMilestoneBacklogItems($milestone);
        $backlog_items_representation = array();

        foreach ($backlog_items as $backlog_item) {
            $backlog_items_representation[] = new BacklogItemRepresentation($backlog_item);
        }

        return array_slice($backlog_items_representation, $offset, $limit);
    }

    /**
     * @url OPTIONS {id}/backlog_items
     *
     * @param int $id Id of the planning
     *
     * @throws 403
     * @throws 404
     */
    protected function optionsBacklogItems($id) {
        $this->getMilestoneById($this->getCurrentUser(), $id);
        $this->sendAllowHeaderForBacklogItems();
    }

    private function getMilestoneById(\PFUser $user, $id) {
        $milestone = $this->milestone_factory->getBareMilestoneByArtifactId($user, $id);

        if (! $milestone) {
            throw new RestException(404);
        }

        if (! $milestone->getArtifact()->userCanView()) {
            throw new RestException(403);
        }

        return $milestone;
    }

    private function getCurrentUser() {
        return UserManager::instance()->getCurrentUser();
    }

    private function getMilestoneBacklogItems($milestone) {
        $backlog_collection_factory = new \AgileDashboard_Milestone_Backlog_BacklogRowCollectionFactory(
            new \AgileDashboard_BacklogItemDao(),
            \Tracker_ArtifactFactory::instance(),
            \Tracker_FormElementFactory::instance(),
            $this->milestone_factory,
            \PlanningFactory::build()
        );

        $strategy_factory = new \AgileDashboard_Milestone_Backlog_BacklogStrategyFactory(
            new \AgileDashboard_BacklogItemDao(),
            \Tracker_ArtifactFactory::instance(),
            \PlanningFactory::build()
        );

        $backlog_strategy = $strategy_factory->getSelfBacklogStrategy($milestone);

        return $backlog_collection_factory->getAllCollection(
            $this->getCurrentUser(),
            $milestone,
            $backlog_strategy,
            ''
        );
    }

    private function checkContentLimit($limit) {
        if (! $this->limitValueIsAcceptable($limit)) {
             throw new RestException(406, 'Maximum value for limit exceeded');
        }
    }

    private function limitValueIsAcceptable($limit) {
        return $limit <= self::MAX_LIMIT;
    }

    private function sendAllowHeaderForBacklogItems() {
        Header::allowOptionsGet();
    }

    private function sendAllowHeaderForSubmilestones() {
        Header::allowOptionsGet();
    }

    private function sendAllowHeadersForMilestone($milestone) {
        $date = $milestone->getLastModifiedDate();
        Header::allowOptionsGet();
        Header::lastModified($date);
    }
}