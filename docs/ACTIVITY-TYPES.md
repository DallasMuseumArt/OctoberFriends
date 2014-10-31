## Activity registration

Activities must be registered in the [Plugin registration file](#registration-file). 
This tells the Friends Plugin about Available Activities and provides a **short name** for using it. 
An example of registering a activity:

```
    public function registerComponents()
    {
        return [
            'DMA\Friends\Activities\ActivityCode' => 'activityCode'
        ];
    }
```

Define the details().  This will report some basic information about your plugin.

```
    public function details()
    {
        return [
            'name'          => 'Activity Code',
            'description'   => 'Complete activities by entering in a code',
        ];
    }
```

If your activity requires additional configuration options create the file field.yaml in a
corresponding folder named after the class.  The file should be named fields.yaml 
see [documentation](https://octobercms.com/docs/backend/forms#field-types) for details.

Example

```
    fields:
        activity_code:
            label: Activity Code
            description: The unique code for this activity.
```

When implementing custom fields you must also define the <strong>getFormDefaultValues()</strong> method to provide default 
values

```
    public function getFormDefaultValues($model)
    {
        return [
            'activity_code' => (isset($model->activity_code)) ? $model->activity_code : null,
        ];
    }
```

When saving an activity with a custom activity type, the class will attempt to map the fields to
attributes on the model.  If you need to further extend this functionality you can implement 
<strong>saveData()</strong> to customize the behavior when an activity form is saved.

See classes in [DMA\Friends\Activities](https://github.com/DallasMuseumArt/OctoberFriends/tree/master/activities) 
as example class for building custom activities

