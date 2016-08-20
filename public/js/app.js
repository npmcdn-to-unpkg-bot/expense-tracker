var { Router,
    Route,
    IndexRoute,
    IndexLink,
    Link,
    browserHistory} = ReactRouter;
require([
    '/js/components/app.js'
], function(App) {
    var destination = document.querySelector("#container");
    ReactDOM.render(
        <Router history={browserHistory}>
            <Route path='/' component={App}>
                <IndexRoute component={Home} />
                <Route path='/category' component={CategoryContainer} />
            </Route>
        </Router>,
        destination
    );
});
