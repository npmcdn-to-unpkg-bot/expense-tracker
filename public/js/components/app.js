define(function() {
    var { Router,
        Route,
        IndexRoute,
        IndexLink,
        Link,
        browserHistory} = ReactRouter;
    
    return React.createClass({
        
        render: function() {
            return (
                <div className='wrapper'>
                    <div className="receipt">
                        <p>Open receipt: SHOP_NAME on RECEIPT_DATE from ACCOUNT_NAME</p>
                    </div>
                    
                </div>
            )
        }
    });
});
