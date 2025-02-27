import React from 'react';
import ConjureComponent from "./conjure-component";

export default class NpcComponentWrapper extends React.Component {

  constructor(props) {
    super(props);
  }

  getComponent() {
    switch(this.props.npcComponentName) {
      case 'Conjure':
        return <ConjureComponent closeComponent={this.props.close} openTimeOutModal={this.props.openTimeOutModal} characterId={this.props.characterId} isDead={this.props.isDead}/>
      default:
        return <div className="alert alert-danger">Component not found. Component Name: {this.props.npcComponentName}</div>
    }
  }

  render() {
    return this.getComponent();
  }
}
