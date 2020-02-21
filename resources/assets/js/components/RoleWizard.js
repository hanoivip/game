import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import RoleList from './RoleList';
export default class RoleWizard extends Component {
	
	constructor(props) {
	    super(props);
	    // this.state is special variable
	    // this.setState is function to switch this variable'
	    // modify state will trigger component updates
	    this.state = {
	      error: null,
	      isLoaded: false,
	      servers: [],
	      selectedSv: "",
	      selectedRole: "",
	    };
	  }
	
	componentDidMount() {
	    fetch("/api/server/list?access_token=" + API_TOKEN, {
	    	method: 'GET',
	    	headers: {
	    		'X-Requested-With': 'XMLHttpRequest',
	    	}
	    })
      .then(res => res.json())
      .then(
        (result) => {
          this.setState({
        	  error: null,
            isLoaded: true,
            servers: result.servers,
          });
        },
        (error) => {
          this.setState({
        	  error: error,
              isLoaded: true,
          });
        }
      )
	}
	
	onSelectServer(e) {
		var sv=e.target.value;
		//console.log("Select new server:" + sv);
		if (sv != "") this.setState({selectedSv:sv});
	}
	
	onSelectRole(e) {
		var role=e.target.value;
		console.log("Select role:" + role);
		if (role!="") this.setState({selectedRole:role});
	}
	
	render() 
    {
    	const isLoaded = this.state.isLoaded;
	    if (isLoaded) {
	    	return this.renderWizard();
	    }
	    else {
	    	return this.renderLoading();
	    }
    }
	
	
	  renderLoading() {
		  return <div className="my-role-wizard"><img src="/img/loading.gif"/></div>;
	  }
	  
	  renderWizard() {
		  const servers = this.state.servers;
		  //const userId = this.props.userId;
		  const selectedSv = this.state.selectedSv;
		  const selectedRole = this.state.selectedRole;
		  const nextUrl = document.getElementById('my-role-wizard').getAttribute('data-next-url');
		  console.log(nextUrl);
		  var list=[];
			servers.map((server) => {
				list.push(<option key={server.name} value={server.name}>{server.title}</option>);
			});
			var button = <br/>;
			if (selectedSv != "" && selectedRole != "")
				button = <button type="submit">Continue</button>
			var roleList = <br/>;
			if (selectedSv != "")
			{
				var roleListId = 'RoleList' + selectedSv;
				// key is very important in component lifetime control
				roleList = <RoleList key={roleListId} server={selectedSv} onSelectRole={(e) => this.onSelectRole(e)}/>;
			}
			if (list.length > 0)
			{
				//console.log("render wizard.." + selectedSv);
				return (
			  			<div className="my-role-wizard">
				  			<form action='/api/wizard/role' method="post">
				  				<input type="hidden" id="next" name="next" value={nextUrl} />
				  				<select id="svname" name="svname" onChange={(e) => this.onSelectServer(e)} value={selectedSv}>
				  					<option value="">Select Server</option>
				  					{list}
			  					</select>
			  					{roleList}
				  				{button}
				  			</form>	
		  				</div>
			  			);
			}
			else
			{
				return (
			  			<div className="my-role-wizard">
			  				<p>There is no server opened! Plz wait!</p>
			  			</div>
			  			);
			}
	  }
	
    
}

if (document.getElementById('my-role-wizard')) {
    ReactDOM.render(<RoleWizard />, document.getElementById('my-role-wizard'));
}
