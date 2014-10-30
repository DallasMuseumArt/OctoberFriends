## Activity registration

[Activities](activities) must be registered in the [Plugin registration file](#registration-file). 
This tells the Friends Plugin about Available Activities and provides a **short name** for using it. 
An example of registering a activity:

    public function registerComponents()
    {
        return [
            'DMA\Friends\Activities\ActivityCode' => 'activityCode'
        ];
    }

See DMA\Friends\Activities\ActivityCode as an example class for building custom activities

