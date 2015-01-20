# View
## View Class

_Copyright (c) 2015, Matthew J. Sahagian_.
_Please reference the LICENSE.md file at the root of this distribution_

#### Namespace

`Inkwell`

#### Imports

<table>

	<tr>
		<th>Alias</th>
		<th>Namespace / Class</th>
	</tr>
	
	<tr>
		<td>Closure</td>
		<td>Closure</td>
	</tr>
	
	<tr>
		<td>ArrayAccess</td>
		<td>ArrayAccess</td>
	</tr>
	
	<tr>
		<td>Flourish</td>
		<td>Dotink\Flourish</td>
	</tr>
	
</table>

#### Authors

<table>
	<thead>
		<th>Name</th>
		<th>Handle</th>
		<th>Email</th>
	</thead>
	<tbody>
	
		<tr>
			<td>
				Matthew J. Sahagian
			</td>
			<td>
				mjs
			</td>
			<td>
				msahagian@dotink.org
			</td>
		</tr>
	
	</tbody>
</table>

## Properties

### Instance Properties
#### <span style="color:#6a6e3d;">$assets</span>

Asset manager instance

#### <span style="color:#6a6e3d;">$components</span>

Sub-components views of this view

#### <span style="color:#6a6e3d;">$container</span>

The container view for this view.

#### <span style="color:#6a6e3d;">$data</span>

The view data accessible via ArrayAccess

#### <span style="color:#6a6e3d;">$level</span>

The current nesting level for templates in this view

#### <span style="color:#6a6e3d;">$template</span>

The template location, relative to root or as absolute path

#### <span style="color:#6a6e3d;">$view</span>

The rendered view including rendered component views




## Methods

### Instance Methods
<hr />

#### <span style="color:#3e6a6e;">__construct()</span>

Create a new view object

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$root_directory
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The root directory for templates
			</td>
		</tr>
					
		<tr>
			<td>
				$assets
			</td>
			<td>
									<a href="http://www.php.net/language.pseudo-types.php">mixed</a>
				
			</td>
			<td>
				The asset manager for adding CSS / JSON
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			void
		</dt>
		<dd>
			Provides no return value.
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">append()</span>

Append a subcomponent to a given element

##### Details

The component can be a relative or absolute path minus the `.php` extension or it
can be a separate view object.  If the component is an independent view object it
will have its own asset manager, data, and subcomponent set.  If it is a template
path it will share the asset manager, data, and subcomponents of this view.

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$element
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The subcomponent stack to append to
			</td>
		</tr>
					
		<tr>
			<td rowspan="3">
				$component
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td rowspan="3">
				The subcomponent to append
			</td>
		</tr>
			
		<tr>
			<td>
									<a href="./">View</a>
				
			</td>
		</tr>
						
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			View
		</dt>
		<dd>
			The called instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">assign()</span>

Assigns a subcomponent to an element

##### Details

Assign a subcomponent, either a `string` or a `View` to given element, overwriting any
existing assignments or previously appended values.  The original subcomponent stack will
be cleared with an empty array.

###### Returns

<dl>
	
		<dt>
			View
		</dt>
		<dd>
			The called instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">compose()</span>

Render the view to a string

##### Details

This will compile the view, inserted subcomponents, injected partials, and potentially
expand container views into a final string representation.

###### Returns

<dl>
	
		<dt>
			string
		</dt>
		<dd>
			The rendered view
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">get()</span>

Get view data with strict requirements

##### Details

Unlike accessing view data via the `ArrayAccess` interface, this method will throw
an exception if the key does not exist and no default is provided.  This is essentially
a mechanism to require data be provided in a given template.

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$key
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The key for the data
			</td>
		</tr>
					
		<tr>
			<td>
				$default
			</td>
			<td>
									<a href="http://www.php.net/language.pseudo-types.php">mixed</a>
				
			</td>
			<td>
				A default value if the data does not exist
			</td>
		</tr>
			
	</tbody>
</table>

###### Throws

<dl>

	<dt>
					Flourish\ProgrammerException		
	</dt>
	<dd>
		If no default is provided and no data exists
	</dd>

</dl>

###### Returns

<dl>
	
		<dt>
			mixed
		</dt>
		<dd>
			The value of the data
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">has()</span>

Determine if a key exists in the data with strict requirements

##### Details

This determines if a piece of data is actually set in the data array as opposed to
the standard `isset` style behavior.

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$key
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The key for which to check the data
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			boolean
		</dt>
		<dd>
			TRUE if the key exists in the data, FALSE otherwise
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">load()</span>

Load the template and optionally mass load components or data

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$template
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The template path relative to root or as an absolute path
			</td>
		</tr>
					
		<tr>
			<td>
				$component
			</td>
			<td>
									<a href="http://www.php.net/language.types.array.php">array</a>
				
			</td>
			<td>
				Component array for mass assignment
			</td>
		</tr>
					
		<tr>
			<td>
				$data
			</td>
			<td>
									<a href="http://www.php.net/language.types.array.php">array</a>
				
			</td>
			<td>
				Data array for mass assignment
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			View
		</dt>
		<dd>
			The called instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">offsetExists()</span>

Check if an offset exists


<hr />

#### <span style="color:#3e6a6e;">offsetGet()</span>

Get an offset

##### Details

Note that when access through this method, even if a key does not exist in the data
array, you will still receive `NULL` as the data.


<hr />

#### <span style="color:#3e6a6e;">offsetSet()</span>

Set an offset

##### Details

Note that if you set a particular offset to `NULL` it will be removed from the data
array.


<hr />

#### <span style="color:#3e6a6e;">offsetUnset()</span>

Unset an offset


<hr />

#### <span style="color:#3e6a6e;">set()</span>

Set view data with strict requirements

##### Details

Unlike assigning view data via the `ArrayAccess` interface, this method will throw
an exception if the key is already present in the data.  This is essentially
a mechanism to prevent certain data from being overloaded.

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$key
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The key for the data
			</td>
		</tr>
					
		<tr>
			<td>
				$value
			</td>
			<td>
									<a href="http://www.php.net/language.pseudo-types.php">mixed</a>
				
			</td>
			<td>
				The value to assign to the data
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			View
		</dt>
		<dd>
			The called instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">buffer()</span>

Buffer the output of an anonymous function inside a template

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$closure
			</td>
			<td>
									Closure				
			</td>
			<td>
				The closure for which to buffer output
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			string
		</dt>
		<dd>
			The buffered output of the function
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">expand()</span>

Tell the view to expand a template using another from inside the template

##### Details

Templates are only expanded if they are not inserted into an existing container at
the time of composition.  This allows a template to be used as both a partial and a
full page.

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$element
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The container template element in which to add the current view
			</td>
		</tr>
					
		<tr>
			<td>
				$template
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The template to load for the containing view
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			View
		</dt>
		<dd>
			The called instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">export()</span>

Export a private property as a reference

##### Details

This is mostly used internally for sharing data or components between templates.

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$property
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The property to export
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			reference
		</dt>
		<dd>
			A reference to the requested property
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">inject()</span>

Inject a template directly from within a template

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$template
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The template to inject as a partial
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			View
		</dt>
		<dd>
			The called instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">insert()</span>

Insert subcomponents at this position

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$element
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The subcomponent stack to insert
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			View
		</dt>
		<dd>
			The called instance for method chaining
		</dd>
	
</dl>






