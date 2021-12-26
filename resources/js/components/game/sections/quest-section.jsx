import React, {Fragment} from 'react';
import Card from "../components/templates/card";
import QuestTree from "./trees/quest-tree";
import AlertInfo from "../components/base/alert-info";
import QuestTreeOverView from "./modals/quest-tree-over-view";
import localforage from "localforage";
import {groupBy, isEmpty} from "lodash";

export default class QuestSection extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      loading: true,
      showQuestTree: false,
      npcs: [],
      firstTab: null,
      completedQuests: [],
      allQuests: [],
      map_name: null,
    }
  }

  componentDidMount() {

    localforage.getItem('all-quests').then((value) => {
      console.log(value);
      if (value === null) {
        this.fetchQuests(0);
      } else {
        this.setState({
          allQuests: value,
        }, () => {
          this.fetchQuests(1);
        })
      }
    }).catch((error) => {
      console.error(error);
    });
  }

  fetchQuests(fetchCompletedTasksOnly) {
    axios.get('/api/map/quests/' + this.props.characterId, {
      params: {completed_quests_only: fetchCompletedTasksOnly}
    }).then((result) => {
      this.setState({
        loading: false,
        completedQuests: result.data.completed_quests,
        map_name: result.data.map_name,
        allQuests: result.data.hasOwnProperty('all_quests') ? result.data.all_quests : this.state.allQuests,
      }, () => {
        if (!fetchCompletedTasksOnly) {
          localforage.setItem('all-quests', result.data.all_quests).catch((err) => {
            console.error('could not save data to local forage for all quests');
            console.error(err);
          });
        }
      });
    }).catch((err) => {
      if (err.hasOwnProperty('response')) {
        const response = err.response;

        if (response.status === 401) {
          return location.reload();
        }

        if (response.status === 429) {
          return this.props.openTimeOutModal()
        }
      }
    });
  }

  hideQuests() {
    this.props.openQuestDetails(false)
  }

  renderTrees(key) {
    const mapQuests = groupBy(this.state.allQuests, 'belongs_to_map_name');

    const childQuests = [];
    const singleQuests = [];

    for (let i = 0; i < mapQuests[key].length; i++) {
      const quest = mapQuests[key][i];

      if (quest.is_parent && isEmpty(quest.child_quests)) {
        singleQuests.push(
          <QuestTree parentQuest={quest} completedQuests={this.state.completedQuests} ignoreNpcCheck={true} />
        )
      } else {
        childQuests.push(
          <QuestTree parentQuest={quest} completedQuests={this.state.completedQuests} ignoreNpcCheck={true} />
        )
      }
    }

    return (
      <div className="row">
        <div className={singleQuests.length > 0 ? 'col-md-6' : 'hide'}>
          <h4 className="tw-font-light">One off quests</h4>
          <hr />
          {singleQuests}
        </div>
        <div className={singleQuests.length > 0 ? 'col-md-6' : 'col-md-12'}>
          <h4 className="tw-font-light">Quest chain</h4>
          <hr />
          {childQuests}
        </div>
      </div>
    )
  }

  manageQuestTree() {
    this.setState({
      showQuestTree: !this.state.showQuestTree
    })
  }

  render() {
    return (
      <Card
        OtherCss="p-3"
        cardTitle="Quests"
        close={this.hideQuests.bind(this)}
        additionalButton={
          <button className="float-right btn btn-primary btn-sm mr-2" onClick={this.manageQuestTree.bind(this)} disabled={this.state.loading}>
            All Quests
          </button>
        }
      >
        {
          this.state.loading ?
            <Fragment>
              <AlertInfo icon={'fas fa-question-circle'} title={"Caution"}>
                <p>
                  This can take a while to load, as we fetch all the quests and their data for you. Once loaded, we store this data in whats called
                  an Indexed DB, all this means for you is that it's store locally as part of your browser cache.
                </p>
                <p>
                  Clearing your browser cache, will force you to sit through this again. Logging in and out will not.
                </p>
                <p>
                  Should quests change, say in an update, this process will have to happen all over again.
                </p>
              </AlertInfo>
              <div className="progress loading-progress mt-2 mb-2" style={{position: 'relative'}}>
                <div className="progress-bar progress-bar-striped indeterminate">
                </div>
              </div>
            </Fragment>
          :
            <Fragment>
              <AlertInfo icon={"fas fa-question-circle"} title={"ATTN!"}>
                <p>
                  <strong>
                    This tree will not update in real time. It is designed as a reference. Opening and closing will update the quest tree.
                  </strong>
                </p>
                <p>
                  Quests with lines separating them running horizontally are individual quests that can be done in any order.
                </p>
                <p>
                  Quests with blue lines separating them are quests that should be, or have to be, done in order. You see the parent skill
                  is unlocked, and the children skills will be locked. As you complete quests, child quests will open up and the completed
                  quests will be colored green with a checkmark beside them.
                </p>
                <p>
                  Once a quest is complete, the one below it, assuming it's a child quest, will open up - all you have to do is meet the requirements and speak to the NPC
                  with the right command and that's it.
                </p>
                <p>
                  Clicking the name of any quest will show you all the relevant details you need to complete that quest,
                  including: locations, monsters to fight, what plane, how to get to said plane, adventures, faction point levels needed,
                  items and where to get them.
                </p>
                <p>
                  You wil also be shown what NPC and where and how to get to them on the plane you are on. You wil also be shown a list of rewards
                  for completing said quest.
                </p>
              </AlertInfo>
              {this.renderTrees(this.state.map_name)}
            </Fragment>
        }
        {
          this.state.showQuestTree ?
            <Fragment>
              <QuestTreeOverView
                allQuests={this.state.allQuests}
                completedQuests={this.state.completedQuests}
                show={this.state.showQuestTree}
                close={this.manageQuestTree.bind(this)}
              />
            </Fragment>
          : null
        }
      </Card>
    );
  }
}
