# Context menu creation

There are three steps of context menu creation:

* create an instance
- write config and render
+ mark context

This is View file for examples of context menu:

~~~

<div class='<?=$context_class?>'>click right here</div>

~~~

## Simple menu
~~~
	/* create an instance */
	$menu = Menu_Context::factory();
	
	/* write config */
	$menu
		->child(							// add child of $menu
			'Address',							// set name
			'/address/'							// set href
		)		
		->child('Comments', '/comments')	// add child
		->child('Diary', '/diary')			// add child
		->render( );						// render main menu
	
	/* mark context: add context classes to View object */
	View::factory('context.example.view', array(
		'context_class' => $menu->context( )
	))->render( );
~~~

## Usage of context variables
~~~
	/* create an instance */
	$menu = Menu_Context::factory();
	
	/* write config */
	$menu	
		->child(							
			'Address',						
			'/address/:id/:action'				// set href and with two stubs (ID and action)
		)		
		->child('Отзывы', '/comments')		
		->child('Справошник', '/diary')		
		->render( );						
	
	/* mark context: add context classes to View object */
	View::factory('context.example.view', array(
		'context_class' => $menu->context(array(
			'id' 		=> 1,				// set @ID value for current context
			'action' 	=> 'edit'			// set @action value for current context
		))
	))->render( );
~~~

## Disable menu item in context
~~~
	/* create an instance */
	$menu = Menu_Context::factory();
	
	/* write config */
	$menu
		->child(							
			'Address',							
			'/address/'							
			'addr'							// mark Menu item 
		)		
		->child('Comments', '/comments')	
		->child('Diary', '/diary')			
		->render( );						
	
	/* mark context: add context classes to View object */
	View::factory('context.example.view', array(
		'context_class' => $menu->context(
			NULL,
			array(							
				'addr'						// add menu item to list of disabled items
			)
		)
	))->render( );
~~~

## Make submenus
~~~
	/* create an instance */
	$menu = Menu_Context::factory();
	
	/* write config */
	$menu
		->child('Address', '/address/:id/:action', 'addr')		// add child of $menu
		->submenu('News', '/news/:id')							// add submenu of $menu (add child and switch to it)
			->child('Global', '/news/global/:id')				// add child of submenu
			->child('Finance', '/news/finance/:id')				// add child of submenu
			->submenu('Beauty', '/news/miracle/:id')			// add 3th level submenu
				->child('Princesses')							// add child
				->submenu('Fairies')							// add 4th level submenu
					->child('Rose')								// add child
					->child('Green')							// add child
					->child('Blue')								// add child
					->parents(1)								// one level up
				->child('Белочки')								// add child of 3th level submenu
				->parents(2)									// two levels up
		->child('Comments', '/comments')						// add child
		->child('Diary', '/diary')								// add child
		->render( );											// render main menu
	
	/* mark context: add context classes to View object */
	View::factory('context.example.view', array(
		'context_class' => $menu->context(
			array(
				'id'		=> 1,
				'action'	=> 'edit'
			),
			array(
				'addr'
			)
		)
	))->render( );
~~~
## Add confirmation
~~~
	/* create an instance */
	$menu = Menu_Context::factory();
	
	/* write config */
	$menu
		->child(							
			'Address',							
			'/address/'							
		)		
		->child('Comments', '/comments')	
		->child('Delete', '/diary')			
			->confirm('Are you sure?')			// message is optional
		->render( );						
	
	/* mark context: add context classes to View object */
	View::factory('context.example.view', array(
		'context_class' => $menu->context( )
	))->render( );
~~~
