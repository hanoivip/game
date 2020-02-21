import React, { Component } from 'react';
import ReactDOM from 'react-dom';
export default class RoleList extends Component {
	
	constructor(props) {
	    super(props);
	    // this.state is special variable
	    // this.setState is function to switch this variable'
	    // modify state will trigger component updates
	    this.reset();
	  }
	
	componentDidMount() {
		this.fetch();
	}
	
	fetch() {
		const sv=this.props.server;
	    fetch("/api/user/role?access_token=" + API_TOKEN + "&svname=" + sv, {
	    	method: 'GET',
	    	headers: {
	    		'X-Requested-With': 'XMLHttpRequest',
	    	}
	    })
      .then(res => res.json())
      .then(
        (result) => {
          this.setState({
            isLoaded: true,
            roles: result.roles
          });
        },
        (error) => {
          this.setState({
            isLoaded: true,
            error: error
          });
        }
      )
	}
	
	componentWillUnmount () {
		//console.log('role list unmount');
	    this.reset();
	}
	
	reset() {
	    this.state = {
	  	      error: null,
	  	      isLoaded: false,
	  	      roles: [],
	  	      selected: ""
	  	    };
	}
	
	refresh(e) {
		this.fetch();
	}
	
	onSelectRole(e) {
		this.setState({selected:e.target.value});
		this.props.onSelectRole(e);
	}
	
	renderRoles() {
		var list=[];
		const selectedRole = this.state.selected;
		const roles = this.state.roles;
		//console.log(roles);
		Object.keys(roles).map((roleId) => {
			list.push(<option key={roleId} value={roleId}>{roles[roleId]}</option>);
		});
		if (list.length>0) {
			return (<div className="my-role-list">
						<select id="role" name="role" value={selectedRole} onChange={(e) => this.onSelectRole(e)}>
							<option key="" value="">Select Character</option>
							{list}
						</select>
						<a onClick={(e) => this.refresh(e)}>Refresh</a>
					</div>);
		}
		else {
			return (<div className="my-role-list">
				<p>You have not any character!</p>
				<a onClick={(e) => this.refresh(e)}>Refresh</a>
			</div>);
		}
	}
	
	
  renderLoading() {
	  return <div className="my-role-list"><img src="/img/loading.gif"/></div>;
  }
	
    render() {
    	const isLoaded = this.state.isLoaded;
	    if (isLoaded) {
	    	return this.renderRoles();
	    } else {
	    	return this.renderLoading();
	    }
    }
}

if (document.getElementById('my-role-list')) {
    ReactDOM.render(<RoleList />, document.getElementById('my-role-list'));
}
