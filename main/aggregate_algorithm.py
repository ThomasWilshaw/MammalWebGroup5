import pymysql
import math

#Calculates aggregate classification and metrics for a given photo_id as laid out in swanson et al
#Input: photo_id is a photo_id, conneciton is a pymysql connection object, preBlank and preFlag are optional paramaters for if the blank and flag dictionaries are precomputed
#Outputs a tuple with all the  
def aggregate_classifications(photo_ID,connection,preBlank=None,preFlag=None):
    c=connection.cursor()
    #Get blank option ids which can be ignored for a lot of things, if not already passed into function
    blankOptions=[]
    if preBlank:
        blankOptions=preBlank
    else:
        sql="SELECT option_id FROM options where struc='noanimal'"
        c.execute(sql)
        blankResults=c.fetchall()
        
        for row in blankResults:
            blankOptions.append(row['option_id'])

    sql="SELECT species,age,gender FROM animal WHERE photo_id="+str(photo_ID)
    c.execute(sql)
    result=c.fetchall()
    
    speciesTally=dict()
    ageTally=dict()
    genderTally=dict()
    numClass=0
    nonBlanks=0
    for row in result:
        rowS=row['species']
        rowA=row['age']
        rowG=row['gender']
        numClass+=1
        if rowS not in blankOptions:
            nonBlanks+=1
        
        if  rowS in speciesTally:
            speciesTally[rowS]=speciesTally[rowS]+1
        else:
            speciesTally[rowS]=1

        if  rowA in ageTally:
            ageTally[rowA]=ageTally[rowA]+1
        else:
            ageTally[rowA]=1

        if  rowG in genderTally:
            genderTally[rowG]=genderTally[rowG]+1
        else:
            genderTally[rowG]=1
    if numClass>0:
        #Gender and age handled vey basically, just select the mode
        if len(ageTally)>0:
            ageValues=list(ageTally.values())
            ageKeys=list(ageTally.keys())

            age=ageKeys[ageValues.index(max(ageValues))]
        else:
            age=0

        if len(genderTally)>0:
            genderValues=list(genderTally.values())
            genderKeys=list(genderTally.keys())

            gender=genderKeys[genderValues.index(max(genderValues))]
        else:
            gender=0

        values=list(speciesTally.values())
        keys=list(speciesTally.keys())
        
        species=keys[values.index(max(values))]

        #According to paper this is correct, strange that blanks are counted?
        support=speciesTally[species]/numClass
        
        blanks=(numClass-nonBlanks)/numClass

        if blanks==1.0:
            evenness=-1.0
        elif support==1.0:
            evenness=0.0
        else:
            pTot=0
            for s in speciesTally:
                if s not in blankOptions:
                    p=speciesTally[s]/(nonBlanks)
                    pTot-=p*math.log(p)
            evenness=pTot/math.log(len(values))
    else:
        #Would need to add an unclassified option to database
        #Kinda nice to have all fields here to more easily see everything that needs doing
        species=-1
        evenness=-1.0
        blanks=-1
        support=-1
        age=-1
        gender=-1

    #Get flag option_ids from options if not already provided
    if preFlag:
        flags=preFlag
    else:
        sql="SELECT * FROM options WHERE struc='flag'"
        c.execute(sql)
        flagResult=c.fetchall()
        flags=dict()
        for row in flagResult:
            flags[row['option_name']]=row['option_id']

    #If this doesn't get set, should be an error
    flag=-1
    
    #Ten blanks=blank
    if numClass-nonBlanks>=10:
        flag=flags['blank']
    #5 Blanks, no other results=blank
    elif numClass-nonBlanks>=5 and len(speciesTally)==1:
        flag=flags['blank']
    else:
        #If species has been set and neither blank classification above has occured, must be either consensus, complete or incomplete
        ##If no species has been set, no flag should be set
        if species!=-1:
            #Ten matching classifications=consensus
            if speciesTally[species]>=10:
                flag=flags['consensus']
            #Valid species but not enough classificaitons=incomplete
            else:
                flag=flags['incomplete']
        #No consensus but lots of classifications=complete
        elif numClass>=25:
            flag=flags['complete']
        #Otherwise not done=incomplete
        else:
            flag=flags['incomplete']

    return (photo_ID,numClass,species,evenness,blanks,support,flag,age,gender)

#-----------Check aggregates against goldstandard set-----------
def checkAgainstGoldStandard(connection):
    with connection.cursor() as c:
        #Get options matching 'like' struc, we want to ignore these as they are not proper classifications
        sql="SELECT option_id FROM options WHERE struc='like'"
        c.execute(sql)
        ignoreResult=c.fetchall()
        ignore=[]
        for row in ignoreResult:
            ignore.append(row['option_id'])

        #Join aggregate table with classifications from gold standard
        sql= "SELECT * from aggregate ag, animal a WHERE a.photo_id=ag.photo_id AND a.person_id=311"
        c.execute(sql)
        result=c.fetchall()
        speciesmatches=0
        gendermatches=0
        agematches=0
        total=0
        completetotal=0
        completematches=0
        #If classification is not useless (a 'like' classification), check if aggregate matches gold standard classification
        for row in result:
            if row['a.species'] not in ignore:
                total+=1
                if row['flag']==167 or row['flag']==166:
                    completetotal+=1
                if row['a.species']==row['species']:
                    speciesmatches+=1
                    if row['flag']==167 or row['flag']==166:
                        completematches+=1
                if row['a.gender']==row['gender']:
                    gendermatches+=1
                if row['a.age']==row['age']:
                    agematches+=1

        print("Agreement of aggregate species with gold standard = " +str((speciesmatches/total)*100)+"%")
        print("Agreement of aggregate gender with gold standard = " +str((gendermatches/total)*100)+"%")
        print("Agreement of aggregate age with gold standard = " +str((agematches/total)*100)+"%")
        print("Agreement of aggregate species with gold standard where aggregates are complete/consensus = " +str((completematches/completetotal)*100)+"%")
#----------------------------------------------------------------

#-----------RUNS ALGORITHM IMPLEMENTATION ON ALL PHOTOS-----------
def aggregateAll(connection):
    with connection.cursor() as cursor:
        #Get number of photos to classify
        sql="SELECT photo_id FROM `animal` ORDER BY photo_id DESC;"
        cursor.execute(sql)
        maxID=cursor.fetchone()['photo_id']

        #Pre search for blank options
        blankOptions=[]
        sql="SELECT option_id FROM options where struc='noanimal'"
        cursor.execute(sql)
        blankResults=cursor.fetchall()    
        for row in blankResults:
            blankOptions.append(row['option_id'])

        #Pre search for flag options
        sql="SELECT * FROM options WHERE struc='flag'"
        cursor.execute(sql)
        flagResult=cursor.fetchall()
        flags=dict()

        insertParams=[]
        for row in flagResult:
            flags[row['option_name']]=row['option_id']
        for id in range(maxID):
            if id%500==0:
                print(id)
            insertParams.append(aggregate_classifications(id,connection,blankOptions,flags))

        cursor.execute("TRUNCATE TABLE aggregate")
        stmt="INSERT INTO aggregate VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
        #Executing this doesn't care how many need to be inserted as it concatenates them all into one statement
        cursor.executemany(stmt,insertParams)
        connection.commit()
#---------------------------------------------------------------

#--------RUN ALGORITHM IMPLEMENTATION ON PHOTO WITH photo_id--------
def aggregateOne(connection,photo_id):
    with connection.cursor() as cursor:
        insertParams=[]
        insertParams.append(aggregate_classifications(photo_id,connection))

        cursor.execute("DELETE FROM aggregate WHERE photo_id="+str(photo_id))
        stmt="INSERT INTO aggregate VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
        cursor.executemany(stmt,insertParams)
        connection.commit()
#---------------------------------------------------------------

#---------RUN ALGORITHM ON PHOTOS WITH p1<=photo_id<=p2---------
def aggregateRange(connection,p1,p2):
    with connection.cursor() as cursor:

        #Pre search for blank options
        blankOptions=[]
        sql="SELECT option_id FROM options where struc='noanimal'"
        cursor.execute(sql)
        blankResults=cursor.fetchall()    
        for row in blankResults:
            blankOptions.append(row['option_id'])

        #Pre search for flag options
        sql="SELECT * FROM options WHERE struc='flag'"
        cursor.execute(sql)
        flagResult=cursor.fetchall()
        flags=dict()

        insertParams=[]
        for row in flagResult:
            flags[row['option_name']]=row['option_id']
        for id in range(p1,p2+1):
            insertParams.append(aggregate_classifications(id,connection,blankOptions,flags))

        ids=[x for x in range(insertParams[0][0],insertParams[-1][0]+1)]
        stmt="DELETE FROM aggregate WHERE photo_id=%s"
        cursor.executemany(stmt,ids)
        stmt="INSERT INTO aggregate VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
        #Executing this doesn't care how many need to be inserted as it concatenates them all into one statement
        cursor.executemany(stmt,insertParams)
        connection.commit()
#---------------------------------------------------------------

connection = pymysql.connect(host='localhost',user='root',password='toot',db='mammalweb2',charset='utf8mb4',cursorclass=pymysql.cursors.DictCursor)

aggregateAll(connection)
aggregateOne(connection,2)
aggregateRange(connection,500,520)
checkAgainstGoldStandard(connection)

connection.close()