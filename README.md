## Moodle BrainMaster
This is a branch of the project 
<p align="center"><a href="https://moodle.org" target="_blank" title="Moodle Website">
  <img src="https://raw.githubusercontent.com/moodle/moodle/main/.github/moodlelogo.svg" alt="The Moodle Logo">
</a></p>

[Moodle][1] is the World's Open Source Learning Platform, widely used around the world by countless universities, schools, companies, and all manner of organisations and individuals.

Moodle is designed to allow educators, administrators and learners to create personalised learning environments with a single robust, secure and integrated system.

## Innovations to main Moodle


### Ability to register answer time

Setting 
$CFG->storetime = true;
in  config.php activates the storing of the response time to every question. More, also the time spent on the feedback is recorded.
All data is easily accessible by the view question_attempt_view stored in the script folder 


### Repetition of wrong anwers

Setting
$CFG->repeat_errors = 3;

in  config.php  will require the student to give three correct answers for each question they fail on the first attempt. Every time the student gives a wrong answer, the consecutive correct answers counter is reset, and they must provide three more correct responses to that same question. The question will only disappear after the student answers correctly three times in a row. The number three can be replaced with a different value. If set to 1, only a single correct response will be required.

### Integration with BrainMaster

Setting
$CFG->BrainMasterService = 'http://192.168.1.175:5000/api/';

in config.php with a BrainMaster service address enables integration with the BrainMaster neural network.
It is also required to set $CFG->storetime = true; when interacting with BrainMaster.

When enabled, the BrainMaster service interacts with Moodle to optimize test proposals and improve the efficiency of the study process. In this mode, all tests are available under the dummy test "BrainMaster," while all other tests are automatically hidden from the course.

BrainMaster operates in two stages:

#### Training Stage

In the first stage, it adopts a default teaching strategy until it reaches a sufficient level of confidence in its predictions about the student's knowledge and study skills.
During this stage, tests are repeatedly proposed until the error rate falls below a configured threshold, at which point new tests are introduced.

#### Full Integration Stage

At this stage, the system selects quizzes based on one of the following modes:

- Tests available from the last lesson, but never attempted
- Tests available but never attempted
- Questions last answered incorrectly in the last lesson
- Questions last answered incorrectly in the last two lessons
- Questions last answered incorrectly
- Questions answered incorrectly in one of the last three attempts
- Focused test on a difficult lesson
- Random test on all previously asked questions

The selection is based on predictions about the student's knowledge state.


## License

Moodle BrainMaster is provided freely as open source software, under version 3 of the GNU General Public License.
