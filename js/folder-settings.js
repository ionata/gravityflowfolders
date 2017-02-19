import React from 'react';
import ReactDOM from 'react-dom';
import update from 'react-addons-update';
import shortid from 'shortid';

import Repeater from './components/repeater/repeater';
import RepeaterItem from './components/repeater/item';
import RadioGroupField from './components/repeater/radio-group';
import CheckboxField from './components/repeater/checkbox';
import SelectField from './components/repeater/select';
import TextField from './components/repeater/text';
import TextAreaField from './components/repeater/textarea';


class FolderSettings extends RepeaterItem {

	constructor(props) {
		super(props);
		this.state = { isOpen : false };
		this._toggle = this._toggle.bind(this);
	}

	_toggle(){
		this.setState( { isOpen: ! this.state.isOpen });
	}

	render(){
		const strings = this.props.strings;

		const permissionsRadioChoices = [
			{
				value: 'all',
				label: strings.allUsers
			}
			,
			{
				value: 'select',
				label: strings.selectUsers
			}
		];

		var settings = '';

		if ( this.state.isOpen ) {

			var selectUsers = '';

			if ( this.props.item.permissions == 'select' ) {
				selectUsers = <SelectField settingName="assignees" value={this.props.item.assignees} choices={this.props.strings.vars.userChoices} multiple={true}/>
			}

			settings = (<div className="gravityflow-folder-settings">
				<TextField settingName="name" value={this.props.item.name} label={strings.folderName} />
				<br />
				Permissions<br />
				<RadioGroupField settingName="permissions" value={this.props.item.permissions} choices={permissionsRadioChoices} horizontal={true} />
				{selectUsers}
			</div>);
		}

		return (<div className="gravityflow-folder-settings-container" key={this.props.item.id}>
				<div className="gravityflow-settings-header" onClick={this._toggle}>
					<div className="gravityflow-toggle-icon"><i className={ this.state.isOpen ? "fa fa-caret-down" : "fa fa-caret-right"} /></div>
				{this.props.item.name}
				</div>
					{settings}
				</div>);
	}
}

jQuery(document).ready(function () {
	function _updateFieldJSON(name, items) {
		document.getElementById('folders').value = JSON.stringify(items);
	}
	let strings = gravityflowfolders_settings_js_strings;
	ReactDOM.render(
		<Repeater
			value={JSON.parse(document.getElementById('folders').value)}
			defaultValues={function(){
				return {
					id: shortid.generate(),
					name: strings.defaultFolderName,
					sequential: true,
					assignees: [],
					permissions: 'all',
					nodes:
						[
							{
								id: shortid.generate(),
								form_id: '',
								custom_label: ''
							}
						]
			}	}}
			onChange={_updateFieldJSON}
			strings={gravityflowfolders_settings_js_strings}
		>
			<FolderSettings strings={gravityflowfolders_settings_js_strings}/>
		</Repeater>,
		document.getElementById('gravityflowfolders-folders-settings-ui')
	);
});


